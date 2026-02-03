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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('bank_name'); // Nombre del banco
            $table->enum('account_type', ['checking', 'savings'])->default('checking');
            $table->string('account_number'); // Número de cuenta
            $table->string('account_holder'); // Titular de la cuenta
            $table->string('cbu')->nullable(); // CBU para Argentina
            $table->string('alias')->nullable(); // Alias de la cuenta
            $table->string('currency', 3)->default('UYU'); // Moneda
            $table->boolean('is_active')->default(true);
            $table->text('instructions')->nullable(); // Instrucciones para el cliente
            $table->timestamps();

            $table->index('restaurant_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
