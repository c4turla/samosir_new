<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 255);
            $table->text('address')->nullable();
            $table->string('distance', 50)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('site_type', ['dermaga', 'tangkahan'])->default('tangkahan');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('site_name');
            $table->index('site_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_sites');
    }
};