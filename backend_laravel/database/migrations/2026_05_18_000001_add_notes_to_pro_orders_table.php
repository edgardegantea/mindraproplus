<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            // Campo para solicitudes Plus (formulario de contacto, no pago)
            $table->json('notes')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
