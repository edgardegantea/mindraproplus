<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración de seguridad: agrega columnas necesarias para solicitudes Plus
 * si aún no existen. Se puede correr aunque las migraciones anteriores
 * ya se hayan ejecutado — no falla ni duplica columnas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_orders', 'plan_slug')) {
                $table->string('plan_slug')->default('pro')->after('billing_period');
            }
            if (!Schema::hasColumn('pro_orders', 'notes')) {
                $table->json('notes')->nullable()->after('admin_notes');
            }
            if (!Schema::hasColumn('pro_orders', 'assigned_admin_id')) {
                $table->unsignedBigInteger('assigned_admin_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('pro_orders', 'status_notes')) {
                $table->text('status_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('pro_orders', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('status_notes');
            }
            if (!Schema::hasColumn('pro_orders', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'features_override')) {
                $table->json('features_override')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            $cols = ['plan_slug', 'notes', 'assigned_admin_id', 'status_notes', 'reviewed_at', 'reviewed_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pro_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'features_override')) {
                $table->dropColumn('features_override');
            }
        });
    }
};
