<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EodReportsExport implements FromArray, WithHeadings
{
    /** @var array<int, array<string, mixed>> */
    private array $rows;

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        // Return rows with numeric keys to keep columns stable.
        // Also ensure ministries is always a printable string.
        return array_map(function ($row) {
            $ministries = $row['ministries'] ?? '';
            if (is_array($ministries)) {
                $ministries = implode(', ', $ministries);
            }

            return [
                Arr::get($row, 'employee_name', ''),
                Arr::get($row, 'department', ''),
                Arr::get($row, 'position', ''),
                Arr::get($row, 'date', ''),
                Arr::get($row, 'accomplishments', ''),
                Arr::get($row, 'tomorrow_plan', ''),
                Arr::get($row, 'blockers', ''),
                $ministries,
                Arr::get($row, 'status', ''),
                Arr::get($row, 'submitted_at', ''),
                Arr::get($row, 'hours_logged', ''),
                Arr::get($row, 'mood_rating', ''),
            ];
        }, $this->rows);
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Department',
            'Position',
            'Report Date',
            'Accomplishments',
            'Tomorrow Plan',
            'Blockers',
            'Ministries',
            'Status',
            'Submitted At',
            'Hours Logged',
            'Mood Rating',
        ];
    }
}


