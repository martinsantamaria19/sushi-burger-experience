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
        // Agregar campos de bloqueo a restaurants
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_active');
            $table->string('blocked_reason')->nullable()->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('blocked_reason');
        });

        // Agregar campos de bloqueo a qr_codes
        if (Schema::hasTable('qr_codes')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('is_active');
                $table->string('blocked_reason')->nullable()->after('is_blocked');
                $table->timestamp('blocked_at')->nullable()->after('blocked_reason');
            });
        }

        // Agregar campos de bloqueo a users (para limitar usuarios en plan free)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('email_verified_at');
                $table->string('blocked_reason')->nullable()->after('is_blocked');
                $table->timestamp('blocked_at')->nullable()->after('blocked_reason');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_reason', 'blocked_at']);
        });

        if (Schema::hasTable('qr_codes')) {
            Schema::table('qr_codes', function (Blueprint $table) {
                $table->dropColumn(['is_blocked', 'blocked_reason', 'blocked_at']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['is_blocked', 'blocked_reason', 'blocked_at']);
            });
        }
    }
};
