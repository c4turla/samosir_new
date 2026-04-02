<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unloadings', function (Blueprint $table) {
            // Change approval_status from enum to boolean
            $table->dropColumn('approval_status');
            $table->boolean('approval_status')->default(false)->after('landing_site_id');
        });
    }

    public function down(): void
    {
        Schema::table('unloadings', function (Blueprint $table) {
            // Revert back to enum
            $table->dropColumn('approval_status');
            $table->enum('approval_status', ['0', '1'])->default('0')->after('landing_site_id');
        });
    }
};
