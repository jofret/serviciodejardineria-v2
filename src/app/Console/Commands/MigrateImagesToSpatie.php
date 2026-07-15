<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToSpatie extends Command
{
    protected $signature = 'migrate:images-to-spatie';
    protected $description = 'Migrate featured_image and gallery_images to Spatie Media Library';

    public function handle()
    {
        $posts = Post::whereNotNull('featured_image')
            ->orWhereNotNull('gallery_images')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts with images found.');
            return;
        }

        foreach ($posts as $post) {
            // Migrar featured_image
            if ($post->featured_image) {
                $path = $post->featured_image;
                if (Storage::disk('public')->exists($path)) {
                    $post->addMedia(storage_path('app/public/' . $path))
                         ->preservingOriginal()
                         ->toMediaCollection('featured');
                    $this->info("Migrated featured_image for post ID {$post->id}");
                } else {
                    $this->warn("File not found: {$path}");
                }
            }

            // Migrar gallery_images
            if ($post->gallery_images && is_array($post->gallery_images)) {
                foreach ($post->gallery_images as $imagePath) {
                    if (Storage::disk('public')->exists($imagePath)) {
                        $post->addMedia(storage_path('app/public/' . $imagePath))
                             ->preservingOriginal()
                             ->toMediaCollection('gallery');
                        $this->info("Migrated gallery image for post ID {$post->id}: {$imagePath}");
                    } else {
                        $this->warn("File not found: {$imagePath}");
                    }
                }
            }

            // Opcional: eliminar los campos originales después de migrar
            // $post->featured_image = null;
            // $post->gallery_images = null;
            // $post->save();
        }

        $this->info('Migration completed.');
    }
}