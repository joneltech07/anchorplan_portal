<?php

namespace Database\Seeders;

use App\Models\DevotionalRecord;
use App\Models\SundayServiceRecord;
use App\Models\User;
use App\Models\WedednesdayPrayerRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SpiritualCurrentDateSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today()->toDateString();
        $currentWed = Carbon::parse($today);
        // Wednesday (0-6 in PHP Carbon: Mon=1 ... Sun=7, but Carbon uses ISO dayOfWeek)
        $currentWed = $currentWed->next(Carbon::WEDNESDAY)->toDateString();
        $currentSun = Carbon::parse($today);
        $currentSun = $currentSun->next(Carbon::SUNDAY)->toDateString();


        $users = User::query()
            ->where('is_active', true)
            ->get(['id']);

        // Devotional for today
        foreach ($users as $u) {
            DevotionalRecord::updateOrCreate(
                ['user_id' => $u->id, 'date' => $today],
                [
                    // The table uses UUID primary keys with no default, so we must provide it on create.
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'status' => 'none',
                    'notes' => null,
                ]
            );

        }


        // Wednesday prayer for the (upcoming) Wednesday of current week
        foreach ($users as $u) {
            $record = WedednesdayPrayerRecord::where('user_id', $u->id)
                ->where('wednesday_date', $currentWed)
                ->first();

            if (! $record) {
                WedednesdayPrayerRecord::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $u->id,
                    'wednesday_date' => $currentWed,
                    'attended' => false,
                    'status' => 'absent',
                    'absence_reason' => null,
                ]);
            } else {
                $record->update([
                    'attended' => false,
                    'status' => 'absent',
                    'absence_reason' => null,
                ]);
            }
        }


        // Sunday service for the (upcoming) Sunday of current week
        foreach ($users as $u) {
            $record = SundayServiceRecord::where('user_id', $u->id)
                ->where('sunday_date', $currentSun)
                ->first();

            if (! $record) {
                SundayServiceRecord::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $u->id,
                    'sunday_date' => $currentSun,
                    'attended' => false,
                    'status' => 'absent',
                    'absence_reason' => null,
                ]);
            } else {
                $record->update([
                    'attended' => false,
                    'status' => 'absent',
                    'absence_reason' => null,
                ]);
            }
        }


    }
}

