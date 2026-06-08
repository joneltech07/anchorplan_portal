<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'requires_eod')) {
                $table->boolean('requires_eod')->default(true)->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'eod_cutoff_time')) {
                $table->time('eod_cutoff_time')->default('18:00:00')->after('requires_eod');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'eod_cutoff_time')) {
                $table->dropColumn('eod_cutoff_time');
            }

            if (Schema::hasColumn('users', 'requires_eod')) {
                $table->dropColumn('requires_eod');
            }
        });
    }
};
