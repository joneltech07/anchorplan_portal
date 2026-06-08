<?php

use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\InventoryController;
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

    // Spiritual Formation
    Route::get('/spiritual', function () {
        return Inertia::render('Spiritual/Index');
    })->name('spiritual.index');
});

require __DIR__.'/auth.php';

