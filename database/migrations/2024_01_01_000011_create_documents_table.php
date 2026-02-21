<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 100)->nullable();
            $table->string('document_number', 100)->nullable();
            $table->date('entry_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('file_path', 255);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('document_type');
            $table->index(['vessel_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};