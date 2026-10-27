<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Puts one notification in many students' bells at once.
 *
 * Writes the same rows Laravel's database channel would, so they read back
 * through $user->notifications like any other, but in one insert per 500
 * students instead of one per student: every insert is a database round
 * trip, and a posting reaches every registered student.
 */
class StudentNotifier
{
    private const ROWS_PER_INSERT = 500;

    /**
     * Notify each of the given students, returning how many were notified.
     *
     * First deletes every notification read more than
     * User::READ_NOTIFICATION_HOURS ago, which the bell no longer shows.
     * Posting is the only regular write to the table and nothing runs on a
     * schedule here, so this is where old rows are cleared.
     *
     * @param  Collection<int, User>  $students
     */
    public function send(Collection $students, Notification $notification): int
    {
        DatabaseNotification::query()->where('read_at', '<=', now()->subHours(User::READ_NOTIFICATION_HOURS))->delete();

        $now = now();

        foreach ($students->chunk(self::ROWS_PER_INSERT) as $chunk) {
            DatabaseNotification::query()->insert($chunk->map(fn (User $student): array => [
                'id' => (string) Str::uuid(),
                'type' => $notification::class,
                'notifiable_type' => $student->getMorphClass(),
                'notifiable_id' => $student->getKey(),
                'data' => json_encode($notification->toArray($student)),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all());
        }

        return $students->count();
    }
}
