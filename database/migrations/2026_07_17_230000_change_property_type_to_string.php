<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('property_type')->default('casa')->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('property_type', [
                'casa', 'departamento', 'oficina', 'campo', 'quinta', 'country', 'otro',
            ])->default('casa')->change();
        });
    }
};
