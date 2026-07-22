<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->boolean('requires_non_compete_clause')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn('requires_non_compete_clause');
        });
    }
};
