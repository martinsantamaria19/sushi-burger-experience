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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('restrict');
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active');
            $table->string('mp_subscription_id')->nullable()->unique(); // ID en MercadoPago
            $table->string('mp_preapproval_id')->nullable()->unique(); // ID de preapproval en MP
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->timestamp('trial_ends_at')->nullable(); // Fin del período de prueba
            $table->timestamp('cancelled_at')->nullable(); // Fecha de cancelación
            $table->timestamp('ends_at')->nullable(); // Fecha de finalización efectiva
            $table->timestamps();

            $table->index('company_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('mp_subscription_id');
            $table->index('mp_preapproval_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

