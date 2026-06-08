<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
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
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $fullName = trim($request->first_name.
            ($request->middle_name ? ' '.$request->middle_name : '').
            ' '.$request->last_name);

        $user = User::create([
            'name' => $fullName,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex,
            'birth_date' => $request->birth_date,
            'birth_place' => $request->birth_place,
            'nationality' => $request->nationality,
            'civil_status' => $request->civil_status,
            'current_address' => $request->current_address,
            'provincial_address' => $request->provincial_address,
            'contact_number' => $request->contact_number,
            'father_full_name' => $request->father_full_name,
            'father_occupation' => $request->father_occupation,
            'mother_full_name' => $request->mother_full_name,
            'mother_occupation' => $request->mother_occupation,
            'guardian' => $request->guardian,
            'guardian_contact_number' => $request->guardian_contact_number,
            'date_employed' => $request->date_employed,
            'employee_status' => $request->employee_status,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
