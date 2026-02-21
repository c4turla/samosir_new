<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrival_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->date('movement_date');
            $table->time('movement_time');
            $table->foreignId('origin_site_id')->nullable()->constrained('landing_sites');
            $table->foreignId('destination_site_id')->constrained('landing_sites');
            $table->string('syahbandar', 255);
            $table->enum('status', ['LABUH', 'BERTOLAK', 'BERGERAK'])->default('LABUH');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('movement_date');
            $table->index(['vessel_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};