<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('vessel_name', 100);
            $table->string('owner_name', 100);
            $table->string('license_number', 100)->nullable();
            $table->string('gt', 10)->nullable();
            $table->string('fishing_gear', 100)->nullable();
            $table->string('selar_mark', 50)->nullable();
            $table->string('vessel_type', 100)->nullable();
            $table->date('sipi_date')->nullable();
            $table->date('sipi_end_date')->nullable();
            $table->string('length', 5)->nullable();
            $table->string('loa', 5)->nullable();
            $table->string('siup_number', 50)->nullable();
            $table->string('vessel_photo', 250)->nullable();
            $table->string('qr_code', 255)->nullable();
            
            // Foreign Keys
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('vessel_name');
            $table->index('approval_status');
            $table->fullText(['vessel_name', 'owner_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};