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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['mercadopago', 'bank_transfer']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded', 'cancelled'])->default('pending');
            $table->string('mp_payment_id')->nullable()->unique(); // ID en MercadoPago
            $table->string('mp_preference_id')->nullable(); // Preference ID de MercadoPago
            $table->string('bank_transfer_reference')->nullable(); // Referencia de transferencia bancaria
            $table->string('bank_transfer_proof')->nullable(); // URL del comprobante de transferencia
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable(); // Datos adicionales del pago
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index('mp_payment_id');
            $table->index('mp_preference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
