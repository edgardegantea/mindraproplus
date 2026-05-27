<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_admin_id')->nullable()->after('user_id');
            $table->text('status_notes')->nullable()->after('notes');
            $table->timestamp('reviewed_at')->nullable()->after('status_notes');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('pro_orders', function (Blueprint $table) {
            $table->dropColumn(['assigned_admin_id', 'status_notes', 'reviewed_at', 'reviewed_by']);
        });
    }
};
