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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('grace_period_ends_at')->nullable()->after('ends_at');
            $table->boolean('in_grace_period')->default(false)->after('grace_period_ends_at');
            $table->timestamp('last_payment_failed_at')->nullable()->after('in_grace_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['grace_period_ends_at', 'in_grace_period', 'last_payment_failed_at']);
        });
    }
};
