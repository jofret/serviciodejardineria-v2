<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->decimal('estimated_price', 12, 2)->nullable()->after('requests_pickup');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn('estimated_price');
        });
    }
};
