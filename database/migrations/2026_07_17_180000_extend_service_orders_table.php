<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable()->change();
            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();

            $table->foreignId('customer_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('relevamiento_id')->nullable()->after('property_id')->constrained('relevamientos')->nullOnDelete();
            $table->string('flow_type')->default('con_relevamiento')->after('relevamiento_id');
            $table->string('time_slot')->nullable()->after('work_date');
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('status')->default('visita_programada')->change();
            $table->date('work_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['relevamiento_id']);
            $table->dropColumn(['customer_id', 'relevamiento_id', 'flow_type', 'time_slot']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable(false)->change();
            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
            $table->string('status')->default('pending')->change();
            $table->date('work_date')->nullable(false)->change();
        });
    }
};
