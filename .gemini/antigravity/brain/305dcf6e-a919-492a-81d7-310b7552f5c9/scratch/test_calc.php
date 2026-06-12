<?php
require __DIR__.'/../../../../../Documents/AnchorPlan/anchorplan_portal/vendor/autoload.php';
$app = require_once __DIR__.'/../../../../../Documents/AnchorPlan/anchorplan_portal/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AttendanceRecord;
use App\Models\ShiftType;
use Carbon\Carbon;

$record = AttendanceRecord::where('date', '2026-06-10')->first();
if (!$record) {
    echo "Record not found\n";
    exit;
}

$shiftType = $record->getShiftType();
if (!$shiftType) {
    echo "Shift type not found\n";
    exit;
}

echo "Shift Type Name: " . $shiftType->name . "\n";
echo "Start time raw: " . $shiftType->start_time . "\n";
echo "End time raw: " . $shiftType->end_time . "\n";

$start = Carbon::parse($shiftType->start_time);
$end = Carbon::parse($shiftType->end_time);
echo "Parsed Start: " . $start->toIso8601String() . "\n";
echo "Parsed End: " . $end->toIso8601String() . "\n";

if ($end->lessThan($start)) {
    echo "End is less than start, adding 1 day...\n";
    $end->addDay();
    echo "New End: " . $end->toIso8601String() . "\n";
}

$diffInSecs = $end->diffInSeconds($start);
echo "Diff in seconds: " . $diffInSecs . "\n";
$hours = $diffInSecs / 3600.0;
echo "Hours before break: " . $hours . "\n";
echo "Break hours raw: " . $shiftType->break_hours . " (Type: " . gettype($shiftType->break_hours) . ")\n";

$shiftHours = max(0, $hours - (float)$shiftType->break_hours);
echo "Final Shift Hours: " . $shiftHours . "\n";

echo "\n--- Clock Times ---\n";
echo "Clock In Raw: " . $record->clock_in_time . "\n";
echo "Clock Out Raw: " . $record->clock_out_time . "\n";

$in = Carbon::parse($record->clock_in_time);
$out = $record->clock_out_time ? Carbon::parse($record->clock_out_time) : Carbon::now();
echo "Parsed In: " . $in->toIso8601String() . "\n";
echo "Parsed Out: " . $out->toIso8601String() . "\n";

$actualSecs = $out->diffInSeconds($in);
echo "Actual seconds (absolute diff): " . $actualSecs . "\n";

// Wait, let's see how actual hours is computed:
// In getActualHours():
// return round($out->diffInSeconds($in) / 3600.0, 4);
echo "Actual Hours (absolute): " . round($actualSecs / 3600.0, 4) . "\n";

// Let's print out what actually happens in the model's getActualHours()
echo "Model getActualHours() returns: " . $record->getActualHours() . "\n";
