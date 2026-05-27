<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'billing_days')) {
                // billing_days = duración real del ciclo de facturación (30 mensual, 365 anual)
                // trial_days   = días de prueba gratuita (no confundir)
                $table->unsignedSmallInteger('billing_days')->default(30)->after('trial_days');
            }
        });

        // Actualizar los valores por defecto según slug
        DB::table('plans')->where('slug', 'free')->update(['billing_days' => 0]);
        DB::table('plans')->where('slug', 'pro')->update(['billing_days' => 30]);
        DB::table('plans')->where('slug', 'plus')->update(['billing_days' => 30]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'billing_days')) {
                $table->dropColumn('billing_days');
            }
        });
    }
};
