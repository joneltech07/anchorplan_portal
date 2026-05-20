<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $requests = LeaveRequest::with(['user', 'approver'])
            ->when(! $user->hasRole(['admin', 'manager', 'hr']), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('start_date', 'desc')
            ->get();

        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Leave/Index', [
            'requests' => $requests,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sick,vacation,casual,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $userId = $request->user()->hasRole(['admin', 'manager', 'hr']) && $request->filled('user_id') ? $request->user_id : $request->user()->id;

        LeaveRequest::create([
            'user_id' => $userId,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorize('update', $leaveRequest);

        $leaveRequest->update(['status' => 'approved', 'approved_by' => auth()->id()]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        $this->authorize('update', $leaveRequest);

        $leaveRequest->update(['status' => 'rejected', 'approved_by' => auth()->id()]);

        return back()->with('success', 'Leave request rejected.');
    }
}
