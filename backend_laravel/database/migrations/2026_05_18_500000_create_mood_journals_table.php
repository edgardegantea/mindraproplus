<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mood_journals')) return;

        Schema::create('mood_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('mood_score');   // 1-5
            $table->string('mood_emoji', 10);            // 😔 😕 😐 🙂 😄
            $table->string('mood_label', 40);            // 'Muy mal' … 'Excelente'
            $table->text('note')->nullable();
            $table->json('tags')->nullable();            // ['ansiedad','trabajo',…]
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_journals');
    }
};
