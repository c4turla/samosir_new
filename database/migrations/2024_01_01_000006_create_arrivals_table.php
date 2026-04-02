<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrivals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('origin', 100);
            $table->date('arrival_date');
            $table->time('arrival_time');
            $table->foreignId('landing_site_id')->constrained('landing_sites');
            
            $table->enum('fish_quality', ['SEGAR', 'BEKU', 'OLAHAN'])->default('SEGAR');
            $table->decimal('average_price', 20, 2)->nullable();
            $table->integer('waste_volume')->nullable()->comment('in kg');
            $table->integer('fish_temperature')->nullable();
            $table->integer('hold_temperature')->nullable();
            
            $table->enum('status', ['TAMBAT', 'LABUH', 'BONGKAR', 'MENGISI PERBEKALAN', 'PERBAIKAN', 'SELESAI'])->default('TAMBAT');
            $table->enum('approval_status', ['0', '1'])->default('0');
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->boolean('is_processed')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('arrival_date');
            $table->index(['vessel_id', 'arrival_date']);
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrivals');
    }
};