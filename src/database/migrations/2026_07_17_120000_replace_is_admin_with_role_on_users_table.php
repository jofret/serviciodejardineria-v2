<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        DB::table('users')->update([
            'role' => DB::raw("CASE WHEN is_admin = 1 THEN 'admin' ELSE 'relevador' END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
        });

        DB::table('users')->update([
            'is_admin' => DB::raw("CASE WHEN role = 'admin' THEN 1 ELSE 0 END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
