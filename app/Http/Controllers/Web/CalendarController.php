<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\EventAttendee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $events = CalendarEvent::with(['creator', 'attendees.user'])
            ->orderBy('start_time')
            ->get();

        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Calendar/Index', [
            'events' => $events,
            'users' => $users,
        ]);
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

        DB::transaction(function () use ($request, $validated) {
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
        });

        return back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, CalendarEvent $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'type' => 'required|in:meeting,holiday,leave,other',
            'attendees' => 'nullable|array',
            'attendees.*' => 'exists:users,id',
        ]);

        DB::transaction(function () use ($event, $validated) {
            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'type' => $validated['type'],
            ]);

            EventAttendee::where('event_id', $event->id)->delete();

            if (! empty($validated['attendees'])) {
                foreach ($validated['attendees'] as $attendeeId) {
                    EventAttendee::create([
                        'event_id' => $event->id,
                        'user_id' => $attendeeId,
                        'response' => 'pending',
                    ]);
                }
            }
        });

        return back()->with('success', 'Event updated successfully.');
    }

    public function destroy(CalendarEvent $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return back()->with('success', 'Event deleted.');
    }
}
