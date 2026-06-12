<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 4)->default(0.0000)->change();
        });

        Schema::table('shift_types', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->default(0.00)->change();
        });

        Schema::table('shift_types', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->change();
        });
    }
};
