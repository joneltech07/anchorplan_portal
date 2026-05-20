<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Attendance Records
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->date('date')->index();
            $table->dateTime('clock_in_time')->nullable();
            $table->dateTime('clock_out_time')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->default('present')->index(); // 'present', 'late', 'absent', 'half_day'
            $table->integer('late_minutes')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'date']);
        });

        // 2. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('type')->index(); // 'sick', 'vacation', 'casual', 'unpaid'
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->string('status')->default('pending')->index(); // 'pending', 'approved', 'rejected'
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // 3. Payroll Periods
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique(); // e.g., "May 2026"
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->string('status')->default('draft')->index(); // 'draft', 'processed', 'paid'
            $table->timestamps();
        });

        // 4. Payroll Items (IMMUTABLE Ledger)
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payroll_period_id');
            $table->uuid('user_id');
            $table->decimal('regular_hours', 8, 2)->default(0.00);
            $table->decimal('overtime_hours', 8, 2)->default(0.00);
            $table->decimal('base_pay', 12, 2)->default(0.00);
            $table->decimal('deductions', 12, 2)->default(0.00);
            $table->decimal('net_pay', 12, 2)->default(0.00);
            $table->timestamps(); // Created_at is immutable ledger timestamp

            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['payroll_period_id', 'user_id']);
        });

        // 5. Tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->string('priority')->default('medium')->index(); // 'low', 'medium', 'high', 'critical'
            $table->string('status')->default('pending')->index(); // 'pending', 'in_progress', 'review', 'completed'
            $table->date('due_date')->nullable()->index();
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
        });

        // 6. Task Comments
        Schema::create('task_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('task_id');
            $table->uuid('user_id');
            $table->text('comment');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 7. Calendar Events
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time')->index();
            $table->dateTime('end_time')->index();
            $table->string('type')->default('meeting')->index(); // 'meeting', 'holiday', 'leave', 'other'
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // 8. Event Attendees
        Schema::create('event_attendees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('event_id');
            $table->uuid('user_id');
            $table->string('response')->default('pending')->index(); // 'pending', 'accepted', 'declined', 'tentative'
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('calendar_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['event_id', 'user_id']);
        });

        // 9. Products
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name')->index();
            $table->integer('current_stock')->default(0);
            $table->integer('min_stock_threshold')->default(5);
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 10. Inventory Movements (IMMUTABLE Log)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('movement_type')->index(); // 'in', 'out'
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reason');
            $table->timestamps(); // created_at provides immutable timestamp log

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('event_attendees');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_records');
    }
};
