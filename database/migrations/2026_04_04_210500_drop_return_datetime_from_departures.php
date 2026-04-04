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
        Schema::table('departures', function (Blueprint $col) {
            if (Schema::hasColumn('departures', 'return_datetime')) {
                $col->dropColumn('return_datetime');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departures', function (Blueprint $col) {
            $col->dateTime('return_datetime')->nullable();
        });
    }
};
