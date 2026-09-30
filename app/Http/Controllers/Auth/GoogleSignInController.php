<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\GoogleIdTokenVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleSignInController extends Controller
{
    /**
     * Sign a student in with the ID token Google's sign-in button handed the browser.
     *
     * Everything about the person comes from the verified token, never from
     * the form: the browser only relays the token. Only CvSU Google Workspace
     * accounts may continue. New students complete registration before an
     * account is created; existing staff accounts can never use this path.
     */
    public function store(Request $request, GoogleIdTokenVerifier $verifier): RedirectResponse
    {
        $request->session()->forget('google_registration');
        $request->validate(['credential' => ['required', 'string', 'max:8192']]);

        $identity = $verifier->verify($request->string('credential')->value());

        if ($identity === null) {
            return $this->deny(__('Google sign-in could not be verified. Please try again.'));
        }

        if (! str_ends_with($identity['email'], '@cvsu.edu.ph') || $identity['hosted_domain'] !== 'cvsu.edu.ph') {
            return $this->deny(__('Please continue with your @cvsu.edu.ph Google account.'));
        }

        $matches = User::query()
            ->where('google_id', $identity['sub'])
            ->orWhere('email', $identity['email'])
            ->get();

        $user = $matches->firstWhere('google_id', $identity['sub']) ?? $matches->first();

        if ($matches->contains(fn (User $match): bool => ! $match->isStudent())) {
            return $this->deny(__('This is an OSAS staff account. Staff sign in with their email and password.'));
        }

        if ($user?->google_id !== null && $user->google_id !== $identity['sub']) {
            return $this->deny(__('This student account is linked to a different Google account.'));
        }

        if ($user === null) {
            $request->session()->regenerate();
            $request->session()->put('google_registration', [
                'identity' => $identity,
                'expires_at' => now()->addMinutes(15)->getTimestamp(),
            ]);

            return redirect()->route('register');
        }

        Auth::login($this->linkStudent($user, $identity['sub']));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
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
