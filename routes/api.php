<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\AttendanceApiController;
use App\Http\Controllers\Api\v1\CalendarApiController;
use App\Http\Controllers\Api\v1\InventoryApiController;
use App\Http\Controllers\Api\v1\LeaveRequestApiController;
use App\Http\Controllers\Api\v1\PayrollApiController;
use App\Http\Controllers\Api\v1\ProductApiController;
use App\Http\Controllers\Api\v1\TaskApiController;
use App\Http\Controllers\Api\v1\UserApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
    Route::get('/attendance/summary', [AttendanceApiController::class, 'summary']);
    Route::post('/attendance/clock-in', [AttendanceApiController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceApiController::class, 'clockOut']);
    Route::get('/attendance/team', [AttendanceApiController::class, 'team']);

    Route::get('/payroll', [PayrollApiController::class, 'index']);
    Route::post('/payroll/periods', [PayrollApiController::class, 'storePeriod']);
    Route::post('/payroll/{id}/calculate', [PayrollApiController::class, 'calculate']);
    Route::get('/payroll/{id}/items', [PayrollApiController::class, 'periodItems']);

    Route::get('/tasks', [TaskApiController::class, 'index']);
    Route::post('/tasks', [TaskApiController::class, 'store']);
    Route::put('/tasks/{task}', [TaskApiController::class, 'update']);
    Route::post('/tasks/{task}/comments', [TaskApiController::class, 'comment']);

    Route::get('/calendar', [CalendarApiController::class, 'index']);
    Route::post('/calendar', [CalendarApiController::class, 'store']);
    Route::put('/calendar/{calendarEvent}', [CalendarApiController::class, 'update']);
    Route::delete('/calendar/{calendarEvent}', [CalendarApiController::class, 'destroy']);

    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{product}', [ProductApiController::class, 'show']);
    Route::get('/products/{product}/movements', [InventoryApiController::class, 'movements']);
    Route::post('/inventory/movements', [InventoryApiController::class, 'storeMovement']);

    Route::get('/leave-requests', [LeaveRequestApiController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestApiController::class, 'store']);
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestApiController::class, 'approve']);
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestApiController::class, 'reject']);

    Route::get('/users/search', [UserApiController::class, 'search']);

    // EOD API
    Route::get('/eod/today', [\App\Http\Controllers\Api\v1\EodApiController::class, 'today']);
    Route::post('/eod', [\App\Http\Controllers\Api\v1\EodApiController::class, 'store']);
    Route::put('/eod/{id}', [\App\Http\Controllers\Api\v1\EodApiController::class, 'update']);
    Route::get('/eod/team', [\App\Http\Controllers\Api\v1\EodApiController::class, 'team']);
    Route::get('/eod/compliance', [\App\Http\Controllers\Api\v1\EodApiController::class, 'compliance']);
});
