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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('mp_payment_id')->unique(); // ID del pago en MercadoPago
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UYU');
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            $table->date('payment_date');
            $table->json('metadata')->nullable(); // Datos adicionales del pago
            $table->timestamps();

            $table->index('subscription_id');
            $table->index('mp_payment_id');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};

