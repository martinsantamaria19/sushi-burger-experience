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
        Schema::create('categories', function (Blueprint $header) {
            $header->id();
            $header->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $header->foreignId('menu_id')->constrained()->onDelete('cascade');
            $header->string('name');
            $header->integer('sort_order')->default(0);
            $header->boolean('is_active')->default(true);
            $header->softDeletes();
            $header->timestamps();

            $header->index('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
