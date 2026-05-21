<?php

namespace App\Providers;

use App\Models\CalendarEvent;
use App\Models\PayrollPeriod;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\Product;
use App\Models\Task;
use App\Policies\CalendarEventPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CalendarEvent::class => CalendarEventPolicy::class,
        PayrollPeriod::class => PayrollPolicy::class,
        AttendanceRecord::class => AttendanceRecordPolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class,
        Product::class => ProductPolicy::class,
        Task::class => TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-payroll', fn ($user) => $user->hasAnyRole(['super_admin', 'hr_manager', 'finance', 'payroll_processor']));
        Gate::define('manage-tasks', fn ($user) => $user->hasAnyRole(['super_admin', 'general_manager', 'department_manager', 'team_lead']));
        Gate::define('adjust-inventory', fn ($user) => $user->hasAnyRole(['super_admin', 'warehouse_manager', 'warehouse_staff']));
    }
}
