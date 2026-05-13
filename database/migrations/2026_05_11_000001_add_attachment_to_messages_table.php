<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Path to uploaded file stored in storage/app/public/chat
            $table->string('file_url')->nullable()->after('body');
            // Original filename for display (e.g. "document.pdf")
            $table->string('file_name')->nullable()->after('file_url');
            // MIME type e.g. "image/jpeg", "application/pdf"
            $table->string('file_type')->nullable()->after('file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['file_url', 'file_name', 'file_type']);
        });
    }
};
