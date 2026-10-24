<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'admin')->update(['role' => 'superadmin']);
        DB::table('users')->where('role', 'user')->update(['role' => 'staff']);
        DB::table('users')->where('role', 'superadmin')->update(['is_admin' => true]);
        DB::table('users')->where('role', '!=', 'superadmin')->update(['is_admin' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'user']);
    }
};
