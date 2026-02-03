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
        Schema::create('qr_codes', function (Blueprint $column) {
            $column->id();
            $column->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $column->string('name');
            $column->string('redirect_slug')->unique();
            $column->integer('scans_count')->default(0);
            $column->boolean('is_active')->default(true);
            $column->softDeletes();
            $column->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
