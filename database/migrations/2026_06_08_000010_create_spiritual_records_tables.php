<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devotional_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('date');
            $table->string('status'); // on_time/late/none
            $table->text('notes')->nullable();
            $table->uuid('monitored_by')->nullable();
            $table->timestamp('monitored_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('monitored_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('wednesday_prayer_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('wednesday_date');
            $table->boolean('attended')->default(false);
            $table->string('status'); // attended/absent/excused
            $table->text('absence_reason')->nullable();
            $table->uuid('monitored_by')->nullable();
            $table->timestamp('monitored_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'wednesday_date']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('monitored_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('sunday_service_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('sunday_date');
            $table->boolean('attended')->default(false);
            $table->string('status'); // attended/absent/excused
            $table->text('absence_reason')->nullable();
            $table->uuid('monitored_by')->nullable();
            $table->timestamp('monitored_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'sunday_date']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('monitored_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sunday_service_records');
        Schema::dropIfExists('wednesday_prayer_records');
        Schema::dropIfExists('devotional_records');
    }
};

