<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->decimal('final_price', 12, 2)->nullable()->after('price');
            $table->text('final_price_notes')->nullable()->after('final_price');
            $table->string('budget_token')->nullable()->unique()->after('final_price_notes');
            $table->timestamp('budget_sent_at')->nullable()->after('budget_token');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn(['final_price', 'final_price_notes', 'budget_token', 'budget_sent_at']);
        });
    }
};
