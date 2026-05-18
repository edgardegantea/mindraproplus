<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            // Plan al que corresponde la orden ('pro' o 'plus').
            // DEFAULT 'pro' para retrocompatibilidad con órdenes anteriores.
            $table->string('plan_slug')->default('pro')->after('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            $table->dropColumn('plan_slug');
        });
    }
};
