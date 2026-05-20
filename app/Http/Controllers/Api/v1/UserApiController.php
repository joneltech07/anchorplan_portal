<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $query = User::where('is_active', true)
            ->when($request->filled('query'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('name', 'ilike', "%{$request->query}%")
                    ->orWhere('email', 'ilike', "%{$request->query}%")
                    ->orWhere('employee_code', 'ilike', "%{$request->query}%");
            }))
            ->orderBy('name')
            ->limit(50)
            ->get();

        return UserResource::collection($query);
    }
}
