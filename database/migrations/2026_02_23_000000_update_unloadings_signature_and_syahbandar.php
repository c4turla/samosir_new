<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unloadings', function (Blueprint $table) {
            // Drop signature column
            $table->dropColumn('signature');
            
            // Change syahbandar from string to foreign_id
            $table->dropColumn('syahbandar');
            $table->foreignId('syahbandar_id')->nullable()->after('reference_number')->constrained('users')->nullOnDelete();
            
            // Add approved_by and approved_at for approval workflow
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('unloadings', function (Blueprint $table) {
            // Revert changes
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at']);
            
            $table->dropForeign(['syahbandar_id']);
            $table->dropColumn('syahbandar_id');
            $table->string('syahbandar', 100)->nullable()->after('reference_number');
            $table->string('signature', 255)->after('notes');
        });
    }
};
