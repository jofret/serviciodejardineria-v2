<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('property_id')->constrained();
            $table->time('scheduled_time_from')->nullable()->after('scheduled_date');
            $table->time('scheduled_time_to')->nullable()->after('scheduled_time_from');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'scheduled_time_from', 'scheduled_time_to']);
        });
    }
};
