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
        Schema::create('restaurants', function (Blueprint $header) {
            $header->id();
            $header->uuid('uuid')->unique();
            $header->string('name');
            $header->string('slug')->unique();
            $header->string('logo_path')->nullable();
            $header->text('address')->nullable();
            $header->boolean('is_active')->default(true);
            $header->json('settings')->nullable();
            $header->softDeletes();
            $header->timestamps();

            $header->index('uuid');
            $header->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
