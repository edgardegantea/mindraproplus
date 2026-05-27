<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Alertas de crisis cuando predicted_probability > 0.75 (requiere plan Plus)
            $table->boolean('crisis_alerts')->default(true);

            // Resumen semanal de bienestar (futuro: job/cron)
            $table->boolean('weekly_summary')->default(true);

            // Recordatorio para hacer evaluaciones GAD-7/PHQ-9 (futuro: push)
            $table->boolean('assessment_reminders')->default(false);

            // Recordatorio diario para mantener la racha (futuro: push)
            $table->boolean('streak_reminders')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
