<?php

namespace App\Notifications;

use App\Models\Scholarship;
use Illuminate\Notifications\Notification;

/**
 * Tells a student, in the bell, that a scholarship they can apply to was posted.
 *
 * Stored in the database only; posting a scholarship sends no email. Every
 * student gets the same content, written for each through StudentNotifier.
 */
class ScholarshipPosted extends Notification
{
    public function __construct(
        public Scholarship $scholarship,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * The scholarship the bell links to, and the title it shows even if the posting is later renamed or removed.
     *
     * @return array{scholarship_id: int, title: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'scholarship_id' => $this->scholarship->id,
            'title' => $this->scholarship->title,
        ];
    }
}
