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
        Schema::create('blocked_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('resource_type'); // 'restaurant', 'qr_code', 'user'
            $table->unsignedBigInteger('resource_id'); // ID del recurso bloqueado
            $table->string('reason')->nullable(); // Razón del bloqueo
            $table->timestamp('blocked_at');
            $table->timestamp('unblocked_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'resource_type']);
            $table->index(['resource_type', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_resources');
    }
};
