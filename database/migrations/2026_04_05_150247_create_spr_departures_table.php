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
        Schema::create('spr_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // The manager who submitted
            
            $table->string('nakhoda_name', 100);
            $table->string('muatan', 255)->nullable(); // Joined field as requested
            
            // Check Point (CP) fields
            $table->date('cp_arrival_date')->nullable();
            $table->string('cp_arrival_stbl', 100)->nullable();
            $table->date('cp_departure_date')->nullable();
            $table->string('cp_departure_stbl', 100)->nullable();
            
            // Physical Check (Cek Fisik) fields
            $table->date('physical_arrival_date')->nullable();
            $table->string('physical_arrival_stbl', 100)->nullable();
            $table->date('physical_departure_date')->nullable();
            $table->string('physical_departure_stbl', 100)->nullable();
            
            $table->text('additional_notes')->nullable();
            $table->dateTime('planned_departure_datetime');
            
            $table->enum('status', ['pending', 'processed', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('vessel_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spr_departures');
    }
};
