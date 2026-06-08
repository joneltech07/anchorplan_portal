<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'discipleship_lead_id')) {
                $table->uuid('discipleship_lead_id')->nullable()->after('manager_id');
                $table->foreign('discipleship_lead_id')->references('id')->on('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'cell_group_name')) {
                $table->string('cell_group_name')->nullable()->after('discipleship_lead_id');
            }

            if (!Schema::hasColumn('users', 'cell_group_role')) {
                $table->string('cell_group_role')->nullable()->after('cell_group_name');
            }

            if (!Schema::hasColumn('users', 'spiritual_birthday')) {
                $table->date('spiritual_birthday')->nullable()->after('cell_group_role');
            }

            if (!Schema::hasColumn('users', 'prayer_partner')) {
                $table->string('prayer_partner')->nullable()->after('spiritual_birthday');
            }

            if (!Schema::hasColumn('users', 'receive_devotional_reminders')) {
                $table->boolean('receive_devotional_reminders')->default(true)->after('prayer_partner');
            }

            if (!Schema::hasColumn('users', 'receive_prayer_reminders')) {
                $table->boolean('receive_prayer_reminders')->default(true)->after('receive_devotional_reminders');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'discipleship_lead_id')) {
                $table->dropForeign(['discipleship_lead_id']);
                $table->dropColumn('discipleship_lead_id');
            }

            foreach (['cell_group_name','cell_group_role','spiritual_birthday','prayer_partner','receive_devotional_reminders','receive_prayer_reminders'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }

        });
    }
};

