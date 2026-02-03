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
        Schema::create('products', function (Blueprint $header) {
            $header->id();
            $header->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $header->foreignId('category_id')->constrained()->onDelete('cascade');
            $header->string('name');
            $header->text('description')->nullable();
            $header->decimal('price', 12, 2)->default(0);
            $header->string('image_path')->nullable();
            $header->integer('sort_order')->default(0);
            $header->boolean('is_active')->default(true);
            $header->softDeletes();
            $header->timestamps();

            $header->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
