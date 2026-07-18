<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->string('category_other')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn('category_other');
        });
    }
};
