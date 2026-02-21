<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrival_catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrival_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fish_species_id')->constrained('fish_species');
            $table->integer('weight_kg')->default(0);
            $table->decimal('estimated_value', 20, 2)->nullable();
            
            $table->timestamps();
            
            $table->index(['arrival_id', 'fish_species_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrival_catches');
    }
};