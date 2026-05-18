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
        Schema::create('inference_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_session_id')->nullable()->constrained('visitor_sessions')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('audio_filename')->nullable();
            $table->bigInteger('audio_size_bytes')->nullable();
            $table->float('audio_duration_seconds')->nullable();
            $table->text('input_text')->nullable();
            $table->text('generated_text')->nullable();
            $table->string('transcription_language')->nullable();
            $table->string('transcription_source')->nullable();
            $table->string('predicted_label')->nullable();
            $table->float('predicted_probability')->nullable();
            $table->string('model_name')->nullable();
            $table->json('notes')->nullable();
            $table->string('emotion_label')->nullable();
            $table->float('emotion_probability')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inference_records');
        Schema::enableForeignKeyConstraints();
    }
};
