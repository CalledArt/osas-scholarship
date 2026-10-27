<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark one of the student's notifications read and open the scholarship it announces.
     *
     * The bell keeps a read notification for User::READ_NOTIFICATION_HOURS,
     * then leaves it out. It is looked up among the student's own, so another
     * student's is a 404 and its existence is never confirmed. A scholarship
     * removed since it was posted opens the Scholarships list instead.
     */
    public function read(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $scholarship = Scholarship::find($notification->data['scholarship_id'] ?? null);

        return $scholarship === null
            ? redirect()->route('scholarships.index')
            : redirect()->route('scholarships.show', $scholarship);
    }

    /**
     * Mark every unread notification the student has as read, in one update.
     */
    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
