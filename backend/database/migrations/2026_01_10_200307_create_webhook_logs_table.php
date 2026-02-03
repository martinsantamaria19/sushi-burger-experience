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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mp_request_id')->unique(); // x-request-id de MercadoPago para idempotencia
            $table->string('event_type'); // payment.approved, payment.rejected, etc.
            $table->string('resource_type')->nullable(); // payment, subscription, etc.
            $table->string('resource_id')->nullable(); // ID del recurso en MP
            $table->json('payload'); // Payload completo del webhook
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('processing_time_ms')->nullable(); // Tiempo de procesamiento
            $table->timestamps();

            $table->index('mp_request_id');
            $table->index('event_type');
            $table->index('resource_type');
            $table->index('resource_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};

