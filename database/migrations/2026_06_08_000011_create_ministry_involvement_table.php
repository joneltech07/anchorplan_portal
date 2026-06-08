<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministry_involvement', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('eod_date');

            $table->string('ministry_type');
            $table->text('custom_description')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['eod_date']);
            $table->index(['ministry_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministry_involvement');
    }
};

