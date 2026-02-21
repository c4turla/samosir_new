<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->text('address')->nullable();
            $table->string('id_card', 255)->nullable();
            $table->string('authorization_letter', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['user_id', 'vessel_id']);
            $table->index(['user_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_managers');
    }
};