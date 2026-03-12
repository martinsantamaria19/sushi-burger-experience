<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->onDelete('cascade');
            $table->boolean('gluten_free')->default(false)->after('notes');
        });

        // Quitar uniques para permitir mismo producto con distinta variante/gluten_free
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('unique_session_product');
            $table->dropUnique('unique_user_product');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['product_variant_id', 'gluten_free']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['session_id', 'product_id'], 'unique_session_product');
            $table->unique(['user_id', 'product_id'], 'unique_user_product');
        });
    }
};
