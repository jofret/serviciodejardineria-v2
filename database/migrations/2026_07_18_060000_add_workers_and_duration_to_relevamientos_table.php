<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->unsignedInteger('workers_count')->nullable()->after('estimated_price');
            $table->unsignedInteger('estimated_duration_days')->nullable()->after('workers_count');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn(['workers_count', 'estimated_duration_days']);
        });
    }
};
