<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un relevamiento marcado 'enviado' directamente desde el campo status
        // de Filament (sin pasar por Relevamiento::markAsSubmitted()) podía no
        // tener submitted_at cargado; a partir de ahora ese campo es la única
        // fuente de verdad de si el relevador ya completó la visita.
        DB::table('relevamientos')
            ->where('status', 'enviado')
            ->whereNull('submitted_at')
            ->update(['submitted_at' => now()]);

        DB::statement(<<<'SQL'
            UPDATE relevamientos SET status = CASE
                WHEN status = 'borrador' THEN 'pendiente'
                WHEN status IN ('pendiente', 'enviado') THEN 'enviado_a_relevador'
                ELSE status
            END
        SQL);

        Schema::table('relevamientos', function (Blueprint $table) {
            $table->string('status')->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->string('status')->default('borrador')->change();
        });

        DB::statement(<<<'SQL'
            UPDATE relevamientos SET status = CASE
                WHEN status = 'enviado_a_relevador' AND submitted_at IS NOT NULL THEN 'enviado'
                WHEN status = 'enviado_a_relevador' THEN 'pendiente'
                WHEN status = 'pendiente' THEN 'borrador'
                ELSE status
            END
        SQL);
    }
};
