<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_service_id')->constrained()->cascadeOnDelete();
            $table->enum('equipment_name', ['keranjang_plastik', 'meja_sortir', 'gerobak', 'timbangan', 'ice_cruiser']);
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index('equipment_service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_items');
    }
};