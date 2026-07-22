<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->timestamp('reopen_requested_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('relevamientos', function (Blueprint $table) {
            $table->dropColumn('reopen_requested_at');
        });
    }
};
