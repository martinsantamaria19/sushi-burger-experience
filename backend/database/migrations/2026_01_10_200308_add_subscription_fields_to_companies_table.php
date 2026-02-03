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
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('settings')->constrained()->onDelete('set null');
            $table->foreignId('plan_id')->nullable()->after('subscription_id')->constrained('subscription_plans')->onDelete('restrict');
            
            $table->index('subscription_id');
            $table->index('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropForeign(['plan_id']);
            $table->dropIndex(['subscription_id']);
            $table->dropIndex(['plan_id']);
            $table->dropColumn(['subscription_id', 'plan_id']);
        });
    }
};

