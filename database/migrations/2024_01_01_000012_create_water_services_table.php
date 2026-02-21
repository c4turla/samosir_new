<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_services', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->date('request_date');
            $table->date('service_date')->nullable();
            $table->integer('volume')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total_payment', 10, 2)->default(0);
            $table->string('requester', 255)->nullable();
            $table->string('field_officer', 255)->nullable();
            $table->string('treasurer', 255)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['order', 'processed', 'completed', 'cancelled'])->default('order');
            
            $table->timestamps();
            
            $table->index('order_number');
            $table->index('status');
            $table->index(['vessel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_services');
    }
};