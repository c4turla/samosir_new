<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('destination', 100);
            $table->integer('crew_count')->default(0);
            $table->date('departure_date')->nullable();
            $table->time('departure_time')->nullable();
            $table->dateTime('departure_datetime');
            $table->dateTime('return_datetime')->nullable();
            $table->foreignId('landing_site_id')->constrained('landing_sites');
            
            $table->string('syahbandar', 100)->nullable();
            $table->string('administrative_officer', 100)->nullable();
            
            // Supplies
            $table->integer('ice_supply')->default(0);
            $table->integer('water_supply')->default(0);
            $table->integer('diesel_supply')->default(0);
            $table->integer('oil_supply')->default(0);
            $table->integer('gasoline_supply')->default(0);
            $table->string('other_supplies', 100)->nullable();
            
            $table->text('notes')->nullable();
            $table->enum('status', ['Sesuai Jadwal', 'Terlambat', 'Dibatalkan'])->default('Sesuai Jadwal');
            $table->enum('approval_status', ['0', '1'])->default('0');
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('signature', 255)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('departure_date');
            $table->index(['vessel_id', 'departure_date']);
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departures');
    }
};