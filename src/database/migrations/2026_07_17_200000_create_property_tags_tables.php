<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('property_tag_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['property_id', 'property_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_tag_links');
        Schema::dropIfExists('property_tags');
    }
};
