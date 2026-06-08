<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('last_name')->nullable()->after('employee_code');
            });
        }

        if (!Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('first_name')->nullable()->after('last_name');
            });
        }

        if (!Schema::hasColumn('users', 'middle_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('first_name');
            });
        }

        if (!Schema::hasColumn('users', 'sex')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('sex', 20)->nullable()->after('middle_name');
            });
        }

        if (!Schema::hasColumn('users', 'birth_date')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('birth_date')->nullable()->after('sex');
            });
        }

        if (!Schema::hasColumn('users', 'birth_place')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('birth_place')->nullable()->after('birth_date');
            });
        }

        if (!Schema::hasColumn('users', 'nationality')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nationality')->nullable()->after('birth_place');
            });
        }

        if (!Schema::hasColumn('users', 'civil_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('civil_status', 50)->nullable()->after('nationality');
            });
        }

        if (!Schema::hasColumn('users', 'current_address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('current_address', 500)->nullable()->after('civil_status');
            });
        }

        if (!Schema::hasColumn('users', 'provincial_address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('provincial_address', 500)->nullable()->after('current_address');
            });
        }

        if (!Schema::hasColumn('users', 'contact_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('contact_number', 50)->nullable()->after('provincial_address');
            });
        }

        if (!Schema::hasColumn('users', 'father_full_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('father_full_name')->nullable()->after('contact_number');
            });
        }

        if (!Schema::hasColumn('users', 'father_occupation')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('father_occupation')->nullable()->after('father_full_name');
            });
        }

        if (!Schema::hasColumn('users', 'mother_full_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('mother_full_name')->nullable()->after('father_occupation');
            });
        }

        if (!Schema::hasColumn('users', 'mother_occupation')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('mother_occupation')->nullable()->after('mother_full_name');
            });
        }

        if (!Schema::hasColumn('users', 'guardian')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('guardian')->nullable()->after('mother_occupation');
            });
        }

        if (!Schema::hasColumn('users', 'guardian_contact_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('guardian_contact_number', 50)->nullable()->after('guardian');
            });
        }

        if (!Schema::hasColumn('users', 'date_employed')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('date_employed')->nullable()->after('guardian_contact_number');
            });
        }

        if (!Schema::hasColumn('users', 'employee_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employee_status', 50)->default('Trainee')->after('date_employed');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name',
                'first_name',
                'middle_name',
                'sex',
                'birth_date',
                'birth_place',
                'nationality',
                'civil_status',
                'current_address',
                'provincial_address',
                'contact_number',
                'father_full_name',
                'father_occupation',
                'mother_full_name',
                'mother_occupation',
                'guardian',
                'guardian_contact_number',
                'date_employed',
                'employee_status',
            ]);
        });
    }
};
