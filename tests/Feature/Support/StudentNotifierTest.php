<?php

namespace Tests\Feature\Support;

use App\Models\Scholarship;
use App\Models\User;
use App\Notifications\ScholarshipPosted;
use App\Support\StudentNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class StudentNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_every_student_across_more_than_one_insert(): void
    {
        $scholarship = Scholarship::factory()->create(['title' => 'CHED Merit Grant']);
        $students = collect(range(1, 501))->map(fn (int $id): User => (new User)->forceFill(['id' => $id]));

        $notified = app(StudentNotifier::class)->send($students, new ScholarshipPosted($scholarship));

        $this->assertSame(501, $notified);
        $this->assertSame(501, DatabaseNotification::query()->distinct()->count('notifiable_id'));
        $this->assertSame(501, DatabaseNotification::query()->distinct()->count('id'));
        $this->assertSame(
            ['scholarship_id' => $scholarship->id, 'title' => 'CHED Merit Grant'],
            DatabaseNotification::query()->where('notifiable_id', 501)->sole()->data,
        );
    }

    public function test_it_deletes_notifications_read_24_hours_ago_or_more(): void
    {
        $student = User::factory()->create();
        $student->notify(new ScholarshipPosted(Scholarship::factory()->create(['title' => 'Expired Grant'])));
        $student->unreadNotifications()->update(['read_at' => '2026-10-21 09:00:00']);
        $student->notify(new ScholarshipPosted(Scholarship::factory()->create(['title' => 'Recently Read Grant'])));
        $student->unreadNotifications()->update(['read_at' => '2026-10-21 10:00:00']);
        $student->notify(new ScholarshipPosted(Scholarship::factory()->create(['title' => 'Unread Grant'])));
        $this->travelTo('2026-10-22 09:00:00');

        app(StudentNotifier::class)->send(collect([$student]), new ScholarshipPosted(Scholarship::factory()->create(['title' => 'New Grant'])));

        $titles = DatabaseNotification::query()->pluck('data')->pluck('title')->sort()->values()->all();
        $this->assertSame(['New Grant', 'Recently Read Grant', 'Unread Grant'], $titles);
    }
}
