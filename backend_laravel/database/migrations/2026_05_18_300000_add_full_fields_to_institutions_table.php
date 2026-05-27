<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            if (!Schema::hasColumn('institutions', 'type'))
                $table->string('type')->nullable()->after('slug');

            if (!Schema::hasColumn('institutions', 'website'))
                $table->string('website')->nullable()->after('type');

            if (!Schema::hasColumn('institutions', 'contact_name'))
                $table->string('contact_name')->nullable()->after('contact_email');

            if (!Schema::hasColumn('institutions', 'contact_phone'))
                $table->string('contact_phone')->nullable()->after('contact_name');

            if (!Schema::hasColumn('institutions', 'country'))
                $table->string('country')->nullable()->after('contact_phone');

            if (!Schema::hasColumn('institutions', 'state'))
                $table->string('state')->nullable()->after('country');

            if (!Schema::hasColumn('institutions', 'city'))
                $table->string('city')->nullable()->after('state');

            if (!Schema::hasColumn('institutions', 'address'))
                $table->text('address')->nullable()->after('city');

            if (!Schema::hasColumn('institutions', 'max_users'))
                $table->unsignedInteger('max_users')->nullable()->after('address');

            if (!Schema::hasColumn('institutions', 'is_active'))
                $table->boolean('is_active')->default(true)->after('max_users');

            if (!Schema::hasColumn('institutions', 'contract_starts_at'))
                $table->date('contract_starts_at')->nullable()->after('is_active');

            if (!Schema::hasColumn('institutions', 'contract_ends_at'))
                $table->date('contract_ends_at')->nullable()->after('contract_starts_at');

            if (!Schema::hasColumn('institutions', 'notes'))
                $table->text('notes')->nullable()->after('contract_ends_at');

            if (!Schema::hasColumn('institutions', 'logo_url'))
                $table->string('logo_url')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $cols = ['type','website','contact_name','contact_phone','country','state',
                     'city','address','max_users','is_active','contract_starts_at',
                     'contract_ends_at','notes','logo_url'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('institutions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
