<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarApiController extends Controller
{
    public function index(Request $request)
    {
        $events = CalendarEvent::with(['creator', 'attendees.user'])->orderBy('start_time')->get();

        return CalendarEventResource::collection($events);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CalendarEvent::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'type' => 'required|in:meeting,holiday,leave,other',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
        ]);

        $event = DB::transaction(function () use ($request, $validated) {
            $event = CalendarEvent::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'type' => $validated['type'],
                'created_by' => $request->user()->id,
            ]);

            if (! empty($validated['attendees'])) {
                foreach ($validated['attendees'] as $attendeeId) {
                    EventAttendee::create([
                        'event_id' => $event->id,
                        'user_id' => $attendeeId,
                        'response' => 'pending',
                    ]);
                }
            }

            return $event;
        });

        return new CalendarEventResource($event->load(['creator', 'attendees.user']));
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $this->authorize('update', $calendarEvent);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'type' => 'required|in:meeting,holiday,leave,other',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
        ]);

        DB::transaction(function () use ($calendarEvent, $validated) {
            $calendarEvent->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'type' => $validated['type'],
            ]);

            EventAttendee::where('event_id', $calendarEvent->id)->delete();

            if (! empty($validated['attendees'])) {
                foreach ($validated['attendees'] as $attendeeId) {
                    EventAttendee::create([
                        'event_id' => $calendarEvent->id,
                        'user_id' => $attendeeId,
                        'response' => 'pending',
                    ]);
                }
            }
        });

        return new CalendarEventResource($calendarEvent->load(['creator', 'attendees.user']));
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $this->authorize('delete', $calendarEvent);

        $calendarEvent->delete();

        return response()->json(['message' => 'Event deleted.']);
    }
}
