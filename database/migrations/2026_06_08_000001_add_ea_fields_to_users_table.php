<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'supports_executive_id')) {
                $table->uuid('supports_executive_id')->nullable()->after('position');
                $table->foreign('supports_executive_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'employment_type')) {
                $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time')->after('supports_executive_id');
            }
            if (!Schema::hasColumn('users', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('employment_type');
            }
            if (!Schema::hasColumn('users', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supports_executive_id']);
            $table->dropColumn([
                'supports_executive_id',
                'employment_type',
                'contract_start_date',
                'contract_end_date',
            ]);
        });
    }
};
