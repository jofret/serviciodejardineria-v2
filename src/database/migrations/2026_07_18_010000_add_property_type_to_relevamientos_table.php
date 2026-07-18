<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->string('property_type')->nullable()->after('category_id');
        });

        DB::table('relevamientos')->orderBy('id')->each(function ($relevamiento) {
            $propertyType = DB::table('properties')->where('id', $relevamiento->property_id)->value('property_type');

            DB::table('relevamientos')->where('id', $relevamiento->id)->update([
                'property_type' => $propertyType,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn('property_type');
        });
    }
};
