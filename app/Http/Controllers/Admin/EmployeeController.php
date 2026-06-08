<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Display the employee registration form for admin/HR.
     */
    public function create(): Response
    {
        $executives = User::whereIn('role', ['ceo', 'cto', 'general_manager'])
            ->orWhere('position', 'like', '%CEO%')
            ->orWhere('position', 'like', '%CTO%')
            ->get(['id', 'name', 'position']);

        return Inertia::render('Auth/Register', [
            'isAdminRegister' => true,
            'executives' => $executives,
        ]);
    }

    /**
     * Store a newly registered employee by admin/HR.
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'sex' => 'required|string|max:20',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'civil_status' => 'required|string|max:50',
            'current_address' => 'required|string|max:500',
            'provincial_address' => 'nullable|string|max:500',
            'contact_number' => 'required|string|max:50',
            'father_full_name' => 'required|string|max:255',
            'father_occupation' => 'required|string|max:255',
            'mother_full_name' => 'required|string|max:255',
            'mother_occupation' => 'required|string|max:255',
            'guardian' => 'required|string|max:255',
            'guardian_contact_number' => 'required|string|max:50',
            'date_employed' => 'required|date',
            'employee_status' => 'required|in:Trainee,Full Time,Part Time',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'position' => 'required|string|max:255',
            'supports_executive_id' => 'nullable|uuid|exists:users,id',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
        ];

        $data = $request->validate($rules);

        $fullName = trim($data['first_name'] .
            ($data['middle_name'] ? ' ' . $data['middle_name'] : '') .
            ' ' . $data['last_name']);

        // Auto-sets if Executive Assistant
        $department = 'Operations'; // default department
        $managerId = null;

        if ($data['role'] === 'executive_assistant') {
            $department = 'Executive Office';
            $managerId = $data['supports_executive_id'];
        }

        $user = User::create([
            'name' => $fullName,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'sex' => $data['sex'],
            'birth_date' => $data['birth_date'],
            'birth_place' => $data['birth_place'],
            'nationality' => $data['nationality'],
            'civil_status' => $data['civil_status'],
            'current_address' => $data['current_address'],
            'provincial_address' => $data['provincial_address'],
            'contact_number' => $data['contact_number'],
            'father_full_name' => $data['father_full_name'],
            'father_occupation' => $data['father_occupation'],
            'mother_full_name' => $data['mother_full_name'],
            'mother_occupation' => $data['mother_occupation'],
            'guardian' => $data['guardian'],
            'guardian_contact_number' => $data['guardian_contact_number'],
            'date_employed' => $data['date_employed'],
            'employee_status' => $data['employee_status'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'position' => $data['position'],
            'department' => $department,
            'manager_id' => $managerId,
            'supports_executive_id' => $data['supports_executive_id'],
            'employment_type' => $data['employment_type'],
            'contract_start_date' => $data['contract_start_date'],
            'contract_end_date' => $data['contract_end_date'],
            'is_active' => true,
        ]);

        return redirect()->route('dashboard')->with('success', 'Employee registered successfully.');
    }
}
