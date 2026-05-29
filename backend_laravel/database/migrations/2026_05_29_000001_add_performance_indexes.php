<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices compuestos para las consultas más frecuentes de Mindra.
 *
 * Cada índice cubre el WHERE + ORDER BY más habitual en cada tabla:
 *
 *   inference_records  (user_id, created_at)     → historial, trends, weekly report
 *   inference_records  (visitor_session_id, created_at) → chat history agrupado por sesión
 *   mood_journals      (user_id, created_at)     → journal index, stats, wellness score
 *   assessments        (user_id, type, created_at) → latest GAD-7, trends
 *   subscriptions      (user_id, status, expires_at) → activePlan / activeSubscription
 *   visitor_sessions   (user_id, updated_at)     → chatHistory ordenado por más reciente
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inference_records', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'],            'ir_user_created');
            $table->index(['visitor_session_id', 'created_at'], 'ir_session_created');
        });

        Schema::table('mood_journals', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'mj_user_created');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->index(['user_id', 'type', 'created_at'], 'ass_user_type_created');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'expires_at'], 'sub_user_status_expires');
        });

        Schema::table('visitor_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'updated_at'], 'vs_user_updated');
        });
    }

    public function down(): void
    {
        Schema::table('inference_records', function (Blueprint $table) {
            $table->dropIndex('ir_user_created');
            $table->dropIndex('ir_session_created');
        });

        Schema::table('mood_journals', function (Blueprint $table) {
            $table->dropIndex('mj_user_created');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropIndex('ass_user_type_created');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('sub_user_status_expires');
        });

        Schema::table('visitor_sessions', function (Blueprint $table) {
            $table->dropIndex('vs_user_updated');
        });
    }
};
