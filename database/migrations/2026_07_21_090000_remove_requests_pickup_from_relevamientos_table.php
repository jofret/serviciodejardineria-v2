<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn('requests_pickup');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->boolean('requests_pickup')->default(false)->after('requires_non_compete_clause');
        });
    }
};
