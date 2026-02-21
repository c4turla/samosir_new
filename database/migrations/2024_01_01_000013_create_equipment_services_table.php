<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_services', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->string('renter_name', 255);
            $table->foreignId('vessel_id')->nullable()->constrained()->nullOnDelete();
            $table->date('service_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('officer', 255)->nullable();
            $table->string('treasurer', 255)->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['order', 'processed', 'completed', 'cancelled'])->default('order');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index('order_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_services');
    }
};