<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorialController extends Controller
{
    /**
     * Display the practice tour for the user's role.
     *
     * The tour plays on sample screens built from in-memory data and posts
     * nothing, so it cannot create, change or decide any real record.
     */
    public function show(): View
    {
        return view('tutorial.show');
    }

    /**
     * Record that the user has seen the tour, then return them to their dashboard.
     *
     * Only the first completion is kept: replaying the tour later doesn't move it.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->tutorial_completed_at === null) {
            $user->forceFill(['tutorial_completed_at' => now()])->save();
        }

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard');
    }
}
