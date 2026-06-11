<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Shift types table (templates)
        Schema::create('shift_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Night Shift US", "Morning Shift", "Mid Shift"
            $table->string('code')->unique(); // e.g., "NIGHT-US", "MORNING"
            $table->time('start_time'); // 20:00 (8:00 PM)
            $table->time('end_time');   // 04:00 (4:00 AM)
            $table->decimal('break_hours', 3, 1)->default(1.0); // 0, 0.5, 1, 1.5, 2
            $table->decimal('hourly_rate', 10, 2); // in PHP (e.g., 150.00)
            $table->decimal('night_differential_rate', 10, 2)->nullable(); // extra pay for night shifts (e.g., 1.10 = 10% extra)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('color')->default('#3B82F6'); // for calendar display
            $table->timestamps();
        });
        
        // 2. Employee shift assignments
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shift_type_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = ongoing/permanent
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignUuid('assigned_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['user_id', 'start_date', 'end_date']);
        });
        
        // 3. Weekly schedule templates (recurring schedules)
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            // Monday to Sunday shift assignments (JSON)
            $table->json('monday')->nullable(); // { shift_type_id: null or uuid, break_hours: 1 }
            $table->json('tuesday')->nullable();
            $table->json('wednesday')->nullable();
            $table->json('thursday')->nullable();
            $table->json('friday')->nullable();
            $table->json('saturday')->nullable();
            $table->json('sunday')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // 4. Schedule exceptions (override for specific dates)
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('exception_date');
            $table->foreignUuid('shift_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['custom_shift', 'day_off', 'holiday', 'half_day']);
            $table->decimal('break_hours', 3, 1)->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'exception_date']);
            $table->index(['exception_date']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('schedule_exceptions');
        Schema::dropIfExists('weekly_schedules');
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('shift_types');
    }
};
