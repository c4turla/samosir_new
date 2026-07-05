<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arrivals', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
        Schema::table('arrivals', function (Blueprint $table) {
            $table->boolean('approval_status')->default(false)->after('status');
            $table->index('approval_status');
        });

        Schema::table('departures', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
        Schema::table('departures', function (Blueprint $table) {
            $table->boolean('approval_status')->default(false)->after('status');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('arrivals', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
        Schema::table('arrivals', function (Blueprint $table) {
            $table->enum('approval_status', ['0', '1'])->default('0')->after('status');
            $table->index('approval_status');
        });

        Schema::table('departures', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
        Schema::table('departures', function (Blueprint $table) {
            $table->enum('approval_status', ['0', '1'])->default('0')->after('status');
            $table->index('approval_status');
        });
    }
};
