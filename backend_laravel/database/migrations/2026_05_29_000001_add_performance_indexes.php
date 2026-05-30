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
        // Cada bloque comprueba (1) que la tabla exista y (2) que el índice
        // NO exista todavía — la migración es segura de re-ejecutar y de
        // aplicar cuando la tabla objetivo aún no ha sido creada en producción.

        if (Schema::hasTable('inference_records')) {
            Schema::table('inference_records', function (Blueprint $table) {
                if (!$this->indexExists('inference_records', 'ir_user_created')) {
                    $table->index(['user_id', 'created_at'], 'ir_user_created');
                }
                if (!$this->indexExists('inference_records', 'ir_session_created')) {
                    $table->index(['visitor_session_id', 'created_at'], 'ir_session_created');
                }
            });
        }

        if (Schema::hasTable('mood_journals')) {
            Schema::table('mood_journals', function (Blueprint $table) {
                if (!$this->indexExists('mood_journals', 'mj_user_created')) {
                    $table->index(['user_id', 'created_at'], 'mj_user_created');
                }
            });
        }

        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if (!$this->indexExists('assessments', 'ass_user_type_created')) {
                    $table->index(['user_id', 'type', 'created_at'], 'ass_user_type_created');
                }
            });
        }

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                if (!$this->indexExists('subscriptions', 'sub_user_status_expires')) {
                    $table->index(['user_id', 'status', 'expires_at'], 'sub_user_status_expires');
                }
            });
        }

        if (Schema::hasTable('visitor_sessions')) {
            Schema::table('visitor_sessions', function (Blueprint $table) {
                if (!$this->indexExists('visitor_sessions', 'vs_user_updated')) {
                    $table->index(['user_id', 'updated_at'], 'vs_user_updated');
                }
            });
        }
    }

    /** Devuelve true si el índice por nombre ya existe en la tabla dada. */
    private function indexExists(string $table, string $indexName): bool
    {
        return collect(\Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]
        ))->isNotEmpty();
    }

    public function down(): void
    {
        if (Schema::hasTable('inference_records')) {
            Schema::table('inference_records', function (Blueprint $table) {
                if ($this->indexExists('inference_records', 'ir_user_created')) {
                    $table->dropIndex('ir_user_created');
                }
                if ($this->indexExists('inference_records', 'ir_session_created')) {
                    $table->dropIndex('ir_session_created');
                }
            });
        }
        if (Schema::hasTable('mood_journals')) {
            Schema::table('mood_journals', function (Blueprint $table) {
                if ($this->indexExists('mood_journals', 'mj_user_created')) {
                    $table->dropIndex('mj_user_created');
                }
            });
        }
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                if ($this->indexExists('assessments', 'ass_user_type_created')) {
                    $table->dropIndex('ass_user_type_created');
                }
            });
        }
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                if ($this->indexExists('subscriptions', 'sub_user_status_expires')) {
                    $table->dropIndex('sub_user_status_expires');
                }
            });
        }
        if (Schema::hasTable('visitor_sessions')) {
            Schema::table('visitor_sessions', function (Blueprint $table) {
                if ($this->indexExists('visitor_sessions', 'vs_user_updated')) {
                    $table->dropIndex('vs_user_updated');
                }
            });
        }
    }
};
