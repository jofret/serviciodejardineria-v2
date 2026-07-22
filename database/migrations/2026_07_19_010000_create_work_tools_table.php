<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_tools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('relevamiento_work_tool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relevamiento_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_tool_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['relevamiento_id', 'work_tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relevamiento_work_tool');
        Schema::dropIfExists('work_tools');
    }
};
