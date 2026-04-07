<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter ENUM safely
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'syahbandar', 'petugas', 'pengelola', 'kepala_pelabuhan') DEFAULT 'pengelola'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'kepala_pelabuhan')->update(['role' => 'pengelola']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'syahbandar', 'petugas', 'pengelola') DEFAULT 'pengelola'");
    }
};
