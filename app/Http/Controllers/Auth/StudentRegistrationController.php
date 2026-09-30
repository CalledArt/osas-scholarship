<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentRegistrationRequest;
use App\Models\User;
use App\Support\PersonName;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentRegistrationController extends Controller
{
    public function create(Request $request): View
    {
        $identity = $request->session()->get('google_registration.identity');

        return view('auth.register', [
            'email' => $identity['email'],
            'nameParts' => PersonName::split($identity['name']),
        ]);
    }

    public function store(StudentRegistrationRequest $request): RedirectResponse
    {
        $identity = $request->session()->get('google_registration.identity');
        $details = $request->validated();

        try {
            $user = User::create([
                ...$details,
                'name' => PersonName::assemble($details['first_name'], $details['middle_name'] ?? null, $details['last_name']),
                'email' => $identity['email'],
                'google_id' => $identity['sub'],
                'password' => null,
                'role' => UserRole::Student,
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'registration' => __('This Google account or student number is already registered. Please sign in again or contact OSAS.'),
            ]);
        }

        $request->session()->forget('google_registration');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
