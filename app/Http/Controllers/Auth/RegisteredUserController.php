<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\PersonName;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new student and sign them in.
     *
     * The account is marked verified on creation: nothing in this app sends
     * mail, so an unverified student would be locked out of every route
     * behind the `verified` middleware with no way to let themselves in. The
     * school-only email rule in RegisterRequest is what vouches for identity
     * instead. `must_change_password` stays false because the student chose
     * this password themselves — that flag exists for the accounts
     * CreateUserCommand issues with a generated one.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            ...$validated,
            'name' => PersonName::assemble(
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ),
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
