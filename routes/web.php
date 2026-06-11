<?php

use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\SpiritualEodController;

use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\EodReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [App\Http\Controllers\Web\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [App\Http\Controllers\Web\AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [App\Http\Controllers\Web\AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/{id}/approve-ot', [App\Http\Controllers\Web\AttendanceController::class, 'approveOt'])->name('attendance.approve-ot');
    Route::post('/attendance/{id}/reject-ot', [App\Http\Controllers\Web\AttendanceController::class, 'rejectOt'])->name('attendance.reject-ot');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/periods', [PayrollController::class, 'storePeriod'])->name('payroll.periods.store');
    Route::post('/payroll/{id}/calculate', [PayrollController::class, 'calculate'])->name('payroll.calculate');
    Route::get('/payroll/{id}/export', [PayrollController::class, 'exportCsv'])->name('payroll.export');
    Route::get('/payroll/{id}', [PayrollController::class, 'show'])->name('payroll.show');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'comment'])->name('tasks.comment');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{calendarEvent}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{calendarEvent}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/movements', [InventoryController::class, 'storeMovement'])->name('inventory.movements.store');

    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('/leave/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
    Route::post('/leave/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leave.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // EOD routes
    Route::get('/eod', [EodReportController::class, 'index'])->name('eod.index');
    Route::get('/eod/create', [EodReportController::class, 'create'])->name('eod.create');
    Route::post('/eod', [EodReportController::class, 'store'])->name('eod.store');
    Route::get('/eod/{eodReport}', [EodReportController::class, 'show'])->name('eod.show');
    Route::get('/eod/{eodReport}/edit', [EodReportController::class, 'edit'])->name('eod.edit');
    Route::put('/eod/{eodReport}', [EodReportController::class, 'update'])->name('eod.update');
    Route::post('/eod/{eodReport}/review', [EodReportController::class, 'review'])->name('eod.review');

    Route::get('/eod/team', [EodReportController::class, 'teamView'])->name('eod.team');
    Route::get('/eod/gm', [EodReportController::class, 'gmView'])->name('eod.gm');
    Route::get('/eod/hr', [EodReportController::class, 'hrView'])->name('eod.hr');

    // Employee EOD view & excel export
    Route::get('/eod/employee', [EodReportController::class, 'employeeEodView'])->name('eod.employee.view');
    Route::get('/eod/employee/data', [EodReportController::class, 'getEmployeeEodData'])->name('eod.employee.data');
    Route::get('/eod/employee/export', [EodReportController::class, 'exportEodReports'])->name('eod.employee.export');

    // Backward-compatible alias (some environments mount differently)
    Route::get('/eod/employee/', [EodReportController::class, 'employeeEodView']);




    // Spiritual Formation

    // Web endpoint wrapper for authenticated session requests
    Route::post('/spiritual/eod-ministry', [SpiritualEodController::class, 'storeEodMinistry'])->name('eod.ministry.store');

    // Spiritual Formation - web routes (return JSON; no /api prefix)
    Route::get('/spiritual/dashboard', [\App\Http\Controllers\Web\SpiritualController::class, 'dashboard']);
    Route::get('/spiritual/devotional/records', [\App\Http\Controllers\Web\SpiritualController::class, 'devotionalRecords']);
    Route::post('/spiritual/devotional/{userId}', [\App\Http\Controllers\Web\SpiritualController::class, 'updateDevotional']);
    Route::post('/spiritual/devotional/remind/{userId}', [\App\Http\Controllers\Web\SpiritualController::class, 'remindDevotional']);
    Route::post('/spiritual/devotional/remind-all', [\App\Http\Controllers\Web\SpiritualController::class, 'remindAll']);

    Route::get('/spiritual/wednesday/records', [\App\Http\Controllers\Web\SpiritualController::class, 'wednesdayRecords']);
    Route::post('/spiritual/wednesday/{userId}', [\App\Http\Controllers\Web\SpiritualController::class, 'updateWednesday']);

    Route::get('/spiritual/sunday/records', [\App\Http\Controllers\Web\SpiritualController::class, 'sundayRecords']);
    Route::post('/spiritual/sunday/{userId}', [\App\Http\Controllers\Web\SpiritualController::class, 'updateSunday']);

    Route::get('/spiritual/ministry/stats', [\App\Http\Controllers\Web\SpiritualController::class, 'ministryStats']);
    Route::get('/spiritual/ministry/reports', [\App\Http\Controllers\Web\SpiritualController::class, 'ministryReports']);

    Route::get('/spiritual', function () {
        return Inertia::render('Spiritual/Index');
    })->name('spiritual.index');

    // Shift Scheduling & Assignment
    Route::get('/shifts', [\App\Http\Controllers\Web\ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/events', [\App\Http\Controllers\Web\ShiftController::class, 'getEvents'])->name('shifts.events');
    Route::post('/shifts/types', [\App\Http\Controllers\Web\ShiftController::class, 'storeShiftType'])->name('shifts.types.store');
    Route::put('/shifts/types/{shiftType}', [\App\Http\Controllers\Web\ShiftController::class, 'updateShiftType'])->name('shifts.types.update');
    Route::delete('/shifts/types/{shiftType}', [\App\Http\Controllers\Web\ShiftController::class, 'destroyShiftType'])->name('shifts.types.destroy');
    Route::post('/shifts/assign', [\App\Http\Controllers\Web\ShiftController::class, 'assignShift'])->name('shifts.assign');
    Route::delete('/shifts/assign/{id}', [\App\Http\Controllers\Web\ShiftController::class, 'deleteAssignment'])->name('shifts.assign.destroy');
    Route::post('/shifts/weekly-schedules', [\App\Http\Controllers\Web\ShiftController::class, 'storeWeeklySchedule'])->name('shifts.weekly-schedules.store');
    Route::delete('/shifts/weekly-schedules/{weeklySchedule}', [\App\Http\Controllers\Web\ShiftController::class, 'destroyWeeklySchedule'])->name('shifts.weekly-schedules.destroy');
    Route::post('/shifts/exceptions', [\App\Http\Controllers\Web\ShiftController::class, 'storeException'])->name('shifts.exceptions.store');
    Route::delete('/shifts/exceptions/{id}', [\App\Http\Controllers\Web\ShiftController::class, 'deleteException'])->name('shifts.exceptions.destroy');
});


require __DIR__.'/auth.php';

