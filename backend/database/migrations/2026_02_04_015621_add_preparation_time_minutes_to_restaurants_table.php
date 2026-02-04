<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tiempo de preparación del pedido (minutos) que se suma al tiempo de ruta para la estimación de entrega.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedSmallInteger('preparation_time_minutes')->default(15)->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('preparation_time_minutes');
        });
    }
};
