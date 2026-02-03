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
        Schema::create('qr_scans', function (Blueprint $column) {
            $column->id();
            $column->foreignId('qr_code_id')->constrained('qr_codes')->onDelete('cascade');
            $column->string('ip_address')->nullable();
            $column->text('user_agent')->nullable();
            $column->timestamp('scanned_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_scans');
    }
};
