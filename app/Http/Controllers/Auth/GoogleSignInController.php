<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\StudentRosterEntry;
use App\Models\User;
use App\Support\GoogleIdTokenVerifier;
use App\Support\PersonName;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleSignInController extends Controller
{
    /**
     * Sign a student in with the ID token Google's sign-in button handed the browser.
     *
     * Everything about the person comes from the verified token, never from
     * the form: the browser only relays the token. Access then depends on the
     * roster, which is checked on every sign-in, so removing a student from it
     * locks them out at their next sign-in. This path only ever signs in
     * students: an admin account with a matching email is refused, so Google
     * can never grant admin rights.
     */
    public function store(Request $request, GoogleIdTokenVerifier $verifier): RedirectResponse
    {
        $request->validate(['credential' => ['required', 'string', 'max:8192']]);

        $identity = $verifier->verify($request->string('credential')->value());

        if ($identity === null) {
            return $this->deny(__('Google sign-in could not be verified. Please try again.'));
        }

        $matches = User::query()
            ->where('google_id', $identity['sub'])
            ->orWhere('email', $identity['email'])
            ->get();

        $user = $matches->firstWhere('google_id', $identity['sub']) ?? $matches->first();

        if ($user !== null && ! $user->isStudent()) {
            return $this->deny(__('This is an OSAS staff account. Staff sign in with their email and password.'));
        }

        if ($user?->google_id !== null && $user->google_id !== $identity['sub']) {
            return $this->deny(__('This student account is linked to a different Google account.'));
        }

        $rosterEntry = StudentRosterEntry::query()->firstWhere('email', $identity['email']);

        if ($rosterEntry === null) {
            return $this->deny(__(':email is not on the OSAS student roster. If you are an enrolled student, contact the OSAS office.', ['email' => $identity['email']]));
        }

        try {
            $user = $user === null
                ? $this->createStudent($identity, $rosterEntry)
                : $this->linkStudent($user, $identity['sub']);
        } catch (UniqueConstraintViolationException) {
            return $this->deny(__('Your student number is already used by another account. Please contact the OSAS office.'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Create a student's account from their roster entry on their first sign-in.
     *
     * There is no password: the account is only ever entered through Google.
     *
     * @param  array{sub: string, email: string, name: ?string}  $identity
     */
    private function createStudent(array $identity, StudentRosterEntry $rosterEntry): User
    {
        $nameParts = $rosterEntry->first_name !== null
            ? $rosterEntry->only(['first_name', 'middle_name', 'last_name'])
            : PersonName::split($identity['name']);

        return User::create([
            ...$nameParts,
            'name' => PersonName::assemble($nameParts['first_name'], $nameParts['middle_name'], $nameParts['last_name']) ?: $identity['email'],
            'email' => $identity['email'],
            'google_id' => $identity['sub'],
            'password' => null,
            'role' => UserRole::Student,
            'email_verified_at' => now(),
            'must_change_password' => false,
            'student_number' => $rosterEntry->student_number,
            'course' => $rosterEntry->course,
            'year_level' => $rosterEntry->year_level,
        ]);
    }

    /**
     * Tie an existing student account to the Google account signing in.
     *
     * Accounts from before Google sign-in may still carry a forced password
     * change from when OSAS issued them a temporary password. That no longer
     * applies, since they never sign in with a password again.
     */
    private function linkStudent(User $user, string $googleId): User
    {
        $user->forceFill([
            'google_id' => $googleId,
            'must_change_password' => false,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return $user;
    }

    private function deny(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['google' => $message]);
    }
}
