<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TutorialController extends Controller
{
    /**
     * Record that the user has finished or dismissed the tutorial, so it stops
     * starting on its own. It stays replayable from the rail either way.
     */
    public function __invoke(Request $request): Response
    {
        $request->user()->forceFill(['tutorial_completed_at' => now()])->save();

        return response()->noContent();
    }
}
