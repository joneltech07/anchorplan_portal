<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('ot_status')->nullable()->default('pending'); // 'pending', 'approved', 'rejected'
            $table->foreignUuid('ot_approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['ot_approved_by']);
            $table->dropColumn(['ot_status', 'ot_approved_by']);
        });
    }
};
