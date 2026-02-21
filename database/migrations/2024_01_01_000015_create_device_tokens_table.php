<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_token', 255);
            $table->enum('device_type', ['android', 'ios']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id', 'device_token']);
            $table->index('device_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};