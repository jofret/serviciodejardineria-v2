<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Post;
use App\Models\Survey;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importa posts/categories/tags/images/clientes de la base del sitio Laravel 8
 * actual (serviciodejardineria.com.ar) preservando los slugs exactos, para que
 * las URLs públicas (/publicaciones/{slug}, /categoria/{slug}) sigan resolviendo
 * a los mismos contenidos que hoy están posicionados en buscadores.
 *
 * Idempotente: se puede correr varias veces sin duplicar (se salta lo que ya
 * existe por slug / por customer.phone), salvo que se pida --fresh.
 */
class ImportLegacyJardineria extends Command
{
    protected $signature = 'jardineria:import-legacy
                            {--host=host.docker.internal : Host de la DB legacy}
                            {--port=3308 : Puerto de la DB legacy}
                            {--database=jardineria : Nombre de la DB legacy}
                            {--username=laravel : Usuario}
                            {--password=secret : Password}
                            {--images-path=/var/www/storage/app/legacy-images : Carpeta con las imágenes legacy ya copiadas a este contenedor}
                            {--fresh : Vacía Category/Tag/Post/Customer/Survey en la base DESTINO antes de importar (solo dev)}';

    protected $description = 'Importa datos del sitio Laravel 8 actual preservando slugs exactos';

    protected array $skippedImages = [];

    public function handle(): int
    {
        config(['database.connections.legacy' => [
            'driver' => 'mysql',
            'host' => $this->option('host'),
            'port' => $this->option('port'),
            'database' => $this->option('database'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            DB::connection('legacy')->getPdo();
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar a la base legacy: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->confirm('Esto borra Category/Tag/Post/Customer/Survey en la base DESTINO (no la legacy). ¿Continuar?')) {
                return self::FAILURE;
            }
            $this->wipeDestination();
        }

        $categoryMap = $this->importCategories();
        $tagMap = $this->importTags();
        $this->importPosts($categoryMap, $tagMap);
        $this->importClientes();

        if ($this->skippedImages) {
            $this->newLine();
            $this->warn(count($this->skippedImages) . ' imágenes no encontradas en --images-path:');
            foreach (array_slice($this->skippedImages, 0, 20) as $s) {
                $this->line(" - {$s}");
            }
        }

        $this->newLine();
        $this->info('Importación completa.');
        return self::SUCCESS;
    }

    protected function wipeDestination(): void
    {
        DB::table('property_post')->delete();
        DB::table('post_tag')->delete();
        DB::table('media')->where('model_type', Post::class)->delete();
        Post::query()->delete();
        Category::query()->delete();
        Tag::query()->delete();
        Survey::query()->delete();
        Customer::where('fuente', 'migracion_legacy')->delete();
    }

    /** @return array<int,int> id legacy => id nuevo */
    protected function importCategories(): array
    {
        $map = [];
        $rows = DB::connection('legacy')->table('categories')->get();

        foreach ($rows as $row) {
            $existing = Category::where('slug', $row->slug)->first();
            if ($existing) {
                $map[$row->id] = $existing->id;
                continue;
            }

            $category = new Category();
            $category->name = $row->name;
            $category->description = $row->body;
            $category->order = 0;
            $category->is_active = true;
            $category->save(); // Sluggable genera un slug provisorio acá (onUpdate:true lo regenera siempre)

            // Se fuerza el slug exacto del sitio actual con una query cruda,
            // sin pasar por Eloquent: así no dispara de nuevo el observer de
            // Sluggable, que si no volvería a pisarlo con el slug autogenerado.
            DB::table('categories')->where('id', $category->id)->update(['slug' => $row->slug]);

            $map[$row->id] = $category->id;
        }

        $this->info(count($map) . ' categorías (' . $rows->count() . ' en origen).');
        return $map;
    }

    /** @return array<int,int> id legacy => id nuevo */
    protected function importTags(): array
    {
        $map = [];
        $rows = DB::connection('legacy')->table('tags')->get();

        foreach ($rows as $row) {
            $existing = Tag::where('slug', $row->slug)->first();
            if ($existing) {
                $map[$row->id] = $existing->id;
                continue;
            }

            $tag = new Tag();
            $tag->name = $row->name;
            $tag->save();

            DB::table('tags')->where('id', $tag->id)->update(['slug' => $row->slug]);

            $map[$row->id] = $tag->id;
        }

        $this->info(count($map) . ' tags (' . $rows->count() . ' en origen).');
        return $map;
    }

    protected function importPosts(array $categoryMap, array $tagMap): void
    {
        $rows = DB::connection('legacy')->table('posts')->get();
        $imagesPath = rtrim($this->option('images-path'), '/');
        $imported = 0;

        foreach ($rows as $row) {
            if (Post::where('slug', $row->slug)->exists()) {
                $imported++;
                continue;
            }

            if (! isset($categoryMap[$row->category_id])) {
                $this->warn("Post #{$row->id} ({$row->slug}) sin categoría mapeada, se omite.");
                continue;
            }

            $post = new Post();
            $post->category_id = $categoryMap[$row->category_id];
            $post->title = $row->name;
            $post->excerpt = $row->excerpt;
            $post->content = $row->body;
            $post->is_published = $row->status === 'PUBLISHED';
            $post->published_at = $row->created_at;
            $post->save(); // Post no tiene onUpdate, pero igual forzamos el slug abajo por consistencia

            DB::table('posts')->where('id', $post->id)->update(['slug' => $row->slug]);
            $post->refresh();

            // Tags (pivot post_tag remapeado por id nuevo)
            $legacyTagIds = DB::connection('legacy')->table('post_tag')->where('post_id', $row->id)->pluck('tag_id');
            $newTagIds = collect($legacyTagIds)->map(fn ($id) => $tagMap[$id] ?? null)->filter()->values();
            if ($newTagIds->isNotEmpty()) {
                $post->tags()->sync($newTagIds);
            }

            // Imágenes: la de menor id es la "principal" (mismo criterio que
            // usaba PageController::post() en el sitio actual) -> featured;
            // el resto -> gallery.
            $legacyImages = DB::connection('legacy')->table('images')
                ->where('post_id', $row->id)
                ->orderBy('id')
                ->get();

            foreach ($legacyImages as $index => $img) {
                $filename = basename(parse_url($img->file, PHP_URL_PATH) ?: $img->file);
                $localPath = $imagesPath . '/' . $filename;

                if (! is_file($localPath)) {
                    $this->skippedImages[] = "{$filename} (post {$row->slug})";
                    continue;
                }

                $collection = $index === 0 ? 'featured' : 'gallery';
                $post->addMedia($localPath)
                    ->preservingOriginal()
                    ->usingFileName($filename)
                    ->toMediaCollection($collection);
            }

            $imported++;
        }

        $this->info("{$imported} posts ({$rows->count()} en origen).");
    }

    protected function importClientes(): void
    {
        $rows = DB::connection('legacy')->table('clientes')->get();
        $imported = 0;

        foreach ($rows as $row) {
            // El testimonio legacy no tiene teléfono real (el form viejo no lo
            // pedía) y Customer.phone es único y es la clave del flujo de
            // WhatsApp nuevo. Se genera un valor placeholder no ambiguo,
            // explícitamente marcado como dato migrado sin verificar, en vez
            // de inventar un teléfono real que podría chocar con un contacto
            // genuino más adelante.
            $phone = 'legacy-cliente-' . $row->id;

            $customer = Customer::where('phone', $phone)->first();
            if ($customer) {
                $imported++;
                continue;
            }

            $customer = Customer::create([
                'name' => $row->name,
                'phone' => $phone,
                'email' => $row->email,
                'customer_type' => 'casa',
                'status' => 'potencial',
                'preferred_contact' => 'whatsapp',
                'fuente' => 'migracion_legacy',
                'metadata' => [
                    'fuente' => 'migracion_legacy',
                    'cliente_legacy_id' => $row->id,
                    'profession' => $row->profession,
                ],
            ]);

            Survey::create([
                'customer_id' => $customer->id,
                'token' => Str::random(32),
                'gender' => match ($row->gender) {
                    'Masculino' => 'masculino',
                    'Femenino' => 'femenino',
                    default => null,
                },
                'occupation' => $row->profession,
                'birthday_month' => $row->month,
                'birthday_day' => $row->day,
                'comment' => $row->body,
                'is_published' => $row->status === 'PUBLISHED',
                'sent_at' => $row->created_at,
                'answered_at' => $row->created_at,
            ]);

            $imported++;
        }

        $this->info("{$imported} testimonios legacy -> Customer+Survey ({$rows->count()} en origen).");
    }
}
