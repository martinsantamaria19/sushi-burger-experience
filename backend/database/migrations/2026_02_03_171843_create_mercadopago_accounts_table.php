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
        Schema::create('mercadopago_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->onDelete('cascade');
            $table->string('access_token'); // Access token de MercadoPago
            $table->string('public_key'); // Public key de MercadoPago
            $table->string('user_id')->nullable(); // ID del usuario en MercadoPago
            $table->string('app_id')->nullable(); // App ID de MercadoPago
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->timestamp('connected_at')->nullable(); // Fecha de conexión
            $table->timestamps();

            $table->index('company_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadopago_accounts');
    }
};
