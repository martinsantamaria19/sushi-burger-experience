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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Ej: SB-1234
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Cliente (puede ser guest)
            $table->string('customer_name'); // Nombre del cliente
            $table->string('customer_email')->nullable();
            $table->string('customer_phone'); // Requerido para contacto
            $table->text('customer_address'); // Dirección de entrega
            $table->decimal('delivery_address_lat', 10, 8)->nullable();
            $table->decimal('delivery_address_lng', 11, 8)->nullable();
            $table->text('delivery_notes')->nullable(); // Instrucciones de entrega
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['mercadopago', 'bank_transfer'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_id')->nullable(); // ID de pago en MercadoPago
            $table->integer('estimated_delivery_time')->nullable(); // Minutos estimados
            $table->timestamp('actual_delivery_time')->nullable();
            $table->text('notes')->nullable(); // Notas del cliente
            $table->string('tracking_token')->unique()->nullable(); // Token para seguimiento público
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index('order_number');
            $table->index('restaurant_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('tracking_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
