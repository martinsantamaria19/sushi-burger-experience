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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index(); // Para carritos no autenticados
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Para carritos de usuarios registrados
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); // Precio snapshot al momento de agregar
            $table->text('notes')->nullable(); // Notas especiales del cliente
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['session_id', 'restaurant_id']);
            $table->index(['user_id', 'restaurant_id']);

            // Evitar duplicados: mismo producto en el mismo carrito
            $table->unique(['session_id', 'product_id'], 'unique_session_product');
            $table->unique(['user_id', 'product_id'], 'unique_user_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
