<?php

namespace App\Services;

use App\Models\EodReport;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EodService
{
    public function generateDraftsForToday()
    {
        $users = User::where('requires_eod', true)->get();
        foreach ($users as $user) {
            EodReport::firstOrCreate([
                'user_id' => $user->id,
                'report_date' => now()->toDateString(),
            ], [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'accomplishments' => '',
                'tomorrow_plan' => '',
                'status' => 'draft',
            ]);
        }
    }

    public function checkLateSubmissions()
    {
        // mark submissions past cutoff as late
        $today = now()->toDateString();
        $reports = EodReport::where('report_date', $today)->whereNull('submitted_at')->get();
        foreach ($reports as $r) {
            $user = $r->user;
            if ($user && $user->eod_cutoff_time && now()->gt(now()->setTimeFromTimeString($user->eod_cutoff_time))) {
                $r->update(['status' => 'late']);
            }
        }
    }

    public function sendReminders($urgent = false)
    {
        // placeholder: iterate and dispatch notifications
        Log::info('EOD reminders sent'.($urgent? ' (urgent)':''));
    }

    public function escalateChronicLate()
    {
        // placeholder
    }

    public function calculateComplianceStats($dateRange)
    {
        // placeholder
        return [];
    }

    public function extractTopBlockers($days = 7)
    {
        // placeholder
        return [];
    }
}
