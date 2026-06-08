<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\EventAttendee;
use App\Models\EodReport;
use App\Notifications\EodReminderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExecutiveAssistantApiController extends Controller
{
    /**
     * Get the list of executives.
     */
    public function getExecutives(): JsonResponse
    {
        $executives = User::whereIn('role', ['ceo', 'cto', 'general_manager'])
            ->orWhere('position', 'like', '%CEO%')
            ->orWhere('position', 'like', '%CTO%')
            ->get(['id', 'name', 'position', 'email']);

        return response()->json($executives);
    }

    /**
     * Get the executive the EA supports.
     */
    public function getExecutive(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'executive_assistant' && $user->supports_executive_id, 403, 'Unauthorized EA access.');

        $executive = User::find($user->supports_executive_id);
        return response()->json($executive);
    }

    /**
     * Get calendar events of the executive.
     */
    public function getExecutiveCalendar(Request $request, string $executiveId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'super_admin' || ($user->role === 'executive_assistant' && $user->supports_executive_id === $executiveId), 403);

        $events = CalendarEvent::where('created_by', $executiveId)
            ->orWhereHas('attendees', function ($q) use ($executiveId) {
                $q->where('user_id', $executiveId);
            })
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($events);
    }

    /**
     * Create a calendar event on behalf of the executive.
     */
    public function createEventOnBehalf(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'executive_assistant' && $user->supports_executive_id, 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'type' => 'required|in:meeting,holiday,leave,other',
        ]);

        $event = CalendarEvent::create(array_merge($data, [
            'created_by' => $user->supports_executive_id,
        ]));

        EventAttendee::create([
            'event_id' => $event->id,
            'user_id' => $user->supports_executive_id,
            'response' => 'accepted',
        ]);

        return response()->json($event, 201);
    }

    /**
     * Get EOD compliance/summary list for executive's team.
     */
    public function getTeamEod(Request $request, string $department): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'executive_assistant' && $user->supports_executive_id, 403);

        $employees = User::where('department', $department)
            ->where('id', '<>', $user->supports_executive_id)
            ->get(['id', 'name', 'email', 'position']);

        $today = now()->toDateString();
        $reports = EodReport::whereDate('report_date', $today)
            ->whereIn('user_id', $employees->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $summary = $employees->map(function ($emp) use ($reports) {
            $report = $reports->get($emp->id);
            return [
                'user_id' => $emp->id,
                'name' => $emp->name,
                'position' => $emp->position,
                'submitted' => !is_null($report),
                'status' => $report ? $report->status : 'missing',
                'report_id' => $report ? $report->id : null,
                'accomplishments' => $report ? $report->accomplishments : null,
                'tomorrow_plan' => $report ? $report->tomorrow_plan : null,
                'blockers' => $report ? $report->blockers : null,
            ];
        });

        return response()->json($summary);
    }

    /**
     * Send EOD reminder to a user.
     */
    public function sendEodReminder(Request $request, string $userId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->role === 'executive_assistant' && $user->supports_executive_id, 403);

        $employee = User::findOrFail($userId);
        $employee->notify(new EodReminderNotification(urgent: true));

        return response()->json(['message' => 'Reminder sent successfully.']);
    }
}
