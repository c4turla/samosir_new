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
        Schema::table('departures', function (Blueprint $table) {
            $table->string('nomor', 50)->nullable()->after('id');
            $table->string('nakhoda_name', 100)->nullable()->after('vessel_id');
            $table->dateTime('arrival_datetime')->nullable()->after('departure_time');
            $table->integer('etmal_days')->default(0)->after('arrival_datetime');
            $table->integer('etmal_hours')->default(0)->after('etmal_days');
            $table->string('floating_status', 100)->nullable()->after('notes');
            $table->string('unloading_status', 100)->nullable()->after('floating_status');
            $table->string('admin_completion', 100)->nullable()->after('unloading_status');
            
            $table->dropColumn('administrative_officer');
        });
    }

    public function down(): void
    {
        Schema::table('departures', function (Blueprint $table) {
            $table->dropColumn(['nomor', 'nakhoda_name', 'arrival_datetime', 'etmal_days', 'etmal_hours', 'floating_status', 'unloading_status', 'admin_completion']);
            $table->string('administrative_officer', 100)->nullable()->after('syahbandar');
        });
    }
};
