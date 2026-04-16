<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('is_grilled_salmon_available')->default(false)->after('is_gluten_free_available');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->boolean('grilled_salmon')->default(false)->after('gluten_free');
        });

        Schema::table('cart_item_variants', function (Blueprint $table) {
            $table->boolean('grilled_salmon')->default(false)->after('gluten_free');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('grilled_salmon')->default(false)->after('gluten_free');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('is_grilled_salmon_available');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('grilled_salmon');
        });

        Schema::table('cart_item_variants', function (Blueprint $table) {
            $table->dropColumn('grilled_salmon');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('grilled_salmon');
        });
    }
};
