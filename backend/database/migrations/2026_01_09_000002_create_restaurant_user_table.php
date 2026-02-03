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
        Schema::create('restaurant_user', function (Blueprint $header) {
            $header->id();
            $header->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $header->foreignId('user_id')->constrained()->onDelete('cascade');
            $header->enum('role', ['owner', 'admin', 'staff'])->default('staff');
            $header->timestamps();

            $header->unique(['restaurant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_user');
    }
};
