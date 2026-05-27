<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('program_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('program_slug', 50);
            $table->unsignedTinyInteger('current_day')->default(0);
            $table->unsignedTinyInteger('total_days')->default(14);
            $table->json('completed_days')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'program_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_enrollments');
    }
};
