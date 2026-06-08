<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\EodReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EodApiController extends Controller
{
    public function today(Request $request)
    {
        $report = EodReport::where('user_id', $request->user()->id)
            ->where('report_date', now()->toDateString())->first();

        return response()->json($report);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'report_date' => 'required|date',
            'accomplishments' => 'required|string',
            'tomorrow_plan' => 'required|string',
        ]);

        $report = EodReport::create(array_merge($data, [
            'id' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]));

        return response()->json($report, 201);
    }

    public function update(Request $request, $id)
    {
        $report = EodReport::findOrFail($id);
        $this->authorize('update', $report);

        $data = $request->only(['accomplishments','tomorrow_plan','blockers','hours_logged','task_ids_completed','mood_rating','status']);
        $report->update($data);

        return response()->json($report);
    }

    public function team(Request $request)
    {
        // basic: return manager's team's reports for today
        $reports = EodReport::where('report_date', now()->toDateString())->get();
        return response()->json($reports);
    }

    public function compliance(Request $request)
    {
        // placeholder compliance stats
        return response()->json(['submitted' => 0, 'expected' => 0]);
    }
}
