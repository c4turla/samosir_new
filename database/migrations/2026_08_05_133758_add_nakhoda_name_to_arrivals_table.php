<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('arrivals', function (Blueprint $table) {
            $table->string('nakhoda_name', 100)->nullable()->after('vessel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arrivals', function (Blueprint $table) {
            $table->dropColumn('nakhoda_name');
        });
    }
};
