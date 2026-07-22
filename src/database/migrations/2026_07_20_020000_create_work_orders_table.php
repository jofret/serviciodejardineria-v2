<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->unique()->constrained()->cascadeOnDelete();

            $table->date('work_date')->nullable();
            $table->string('time_slot')->nullable();

            // programado | en_curso | completado | cancelado | reprogramado
            $table->string('status')->default('programado');

            $table->string('conformity_token')->nullable()->unique();
            $table->timestamp('conformity_sent_at')->nullable();
            $table->timestamp('conformity_confirmed_at')->nullable();

            $table->timestamps();
        });

        Schema::create('work_order_worker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['work_order_id', 'worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_worker');
        Schema::dropIfExists('work_orders');
    }
};
