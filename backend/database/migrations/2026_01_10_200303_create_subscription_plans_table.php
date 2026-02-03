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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // free, premium
            $table->string('slug')->unique(); // free, premium
            $table->decimal('price', 10, 2)->nullable(); // NULL para plan free
            $table->string('mp_subscription_id')->nullable(); // ID de suscripción en MercadoPago
            $table->json('features')->nullable(); // Features disponibles en el plan
            $table->json('limits')->nullable(); // Límites del plan (restaurants, users, qr_codes)
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

