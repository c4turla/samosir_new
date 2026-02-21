<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unloadings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrival_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number', 100)->nullable();
            $table->string('syahbandar', 100)->nullable();
            $table->string('captain_name', 100)->nullable();
            $table->string('identification_mark', 100)->nullable();
            $table->date('unloading_date');
            $table->time('unloading_time')->nullable();
            $table->string('sequence_number', 50)->nullable();
            $table->date('registration_date');
            $table->string('bl_code', 255)->nullable();
            $table->foreignId('landing_site_id')->nullable()->constrained('landing_sites');
            $table->enum('approval_status', ['0', '1'])->default('0');
            $table->string('signature', 255);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('unloading_date');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unloadings');
    }
};