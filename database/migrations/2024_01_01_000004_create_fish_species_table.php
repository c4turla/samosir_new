<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fish_species', function (Blueprint $table) {
            $table->id();
            $table->string('species_name', 255);
            $table->string('local_name', 255)->nullable();
            $table->string('scientific_name', 255)->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('species_name');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fish_species');
    }
};