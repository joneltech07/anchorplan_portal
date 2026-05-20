<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveRequestApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $requests = LeaveRequest::with(['user', 'approver'])
            ->when(! $user->hasRole(['admin', 'manager', 'hr']), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sick,vacation,casual,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $userId = $request->user()->hasRole(['admin', 'manager', 'hr']) && $request->filled('user_id')
            ? $request->user_id
            : $request->user()->id;

        $leave = LeaveRequest::create([
            'user_id' => $userId,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'pending',
        ]);

        return response()->json($leave, 201);
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        if (! auth()->user()->hasRole(['admin', 'manager', 'hr'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $leaveRequest->update(['status' => 'approved', 'approved_by' => auth()->id()]);

        return response()->json($leaveRequest);
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        if (! auth()->user()->hasRole(['admin', 'manager', 'hr'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $leaveRequest->update(['status' => 'rejected', 'approved_by' => auth()->id()]);

        return response()->json($leaveRequest);
    }
}
