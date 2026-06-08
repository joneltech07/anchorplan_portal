<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eod_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('report_date');
            $table->text('accomplishments');
            $table->text('tomorrow_plan');
            $table->text('blockers')->nullable();
            $table->decimal('hours_logged', 5, 2)->nullable();
            $table->json('task_ids_completed')->nullable();
            $table->unsignedTinyInteger('mood_rating')->nullable();
            $table->enum('status', ['draft','submitted','late','reviewed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->text('manager_feedback')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'report_date']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eod_reports');
    }
};
