<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relevamiento_work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relevamiento_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('observations')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relevamiento_work_items');
    }
};
