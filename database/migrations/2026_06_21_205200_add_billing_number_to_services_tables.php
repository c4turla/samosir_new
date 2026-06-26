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
        Schema::table('equipment_services', function (Blueprint $table) {
            $table->string('billing_number')->nullable()->after('notes');
        });

        Schema::table('water_services', function (Blueprint $table) {
            $table->string('billing_number')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_services', function (Blueprint $table) {
            $table->dropColumn('billing_number');
        });

        Schema::table('water_services', function (Blueprint $table) {
            $table->dropColumn('billing_number');
        });
    }
};
