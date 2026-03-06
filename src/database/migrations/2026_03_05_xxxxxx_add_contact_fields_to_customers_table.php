<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Campos principales del formulario de contacto
            $table->string('zona_principal')->nullable()->after('zone');
            $table->string('partido')->nullable()->after('zona_principal');
            $table->string('otra_zona')->nullable()->after('partido');
            $table->string('servicio_interes')->nullable()->after('otra_zona');
            $table->text('mensaje_inicial')->nullable()->after('servicio_interes');
            $table->string('fuente')->default('web')->after('mensaje_inicial'); // web, llamado, referido
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'zona_principal',
                'partido',
                'otra_zona',
                'servicio_interes',
                'mensaje_inicial',
                'fuente'
            ]);
        });
    }
};