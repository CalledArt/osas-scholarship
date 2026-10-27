<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use App\Notifications\ScholarshipPosted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /*
     * The scholarships in these tests are closed, so their titles appear only
     * in the bell and never in the Dashboard's Available to apply list.
     */

    public function test_the_bell_lists_a_students_notifications_newest_first_with_the_unread_count(): void
    {
        $student = User::factory()->create();
        $this->travelTo('2026-10-20 09:00:00');
        $student->notify(new ScholarshipPosted(Scholarship::factory()->closed()->create(['title' => 'Older Grant'])));
        $this->travelTo('2026-10-22 09:00:00');
        $student->notify(new ScholarshipPosted(Scholarship::factory()->closed()->create(['title' => 'Newest Grant'])));

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSeeInOrder(['Welcome back,', '(2 unread)', 'Where you stand']);
        $response->assertSeeInOrder(['Newest Grant', ', unread', 'Older Grant', ', unread']);
        $response->assertSee('action="'.route('notifications.read-all').'"', false);
    }

    public function test_the_bell_shows_the_ten_latest_but_counts_every_unread_notification(): void
    {
        $student = User::factory()->create();
        foreach (range(1, 11) as $day) {
            $this->travelTo(sprintf('2026-10-%02d 09:00:00', $day));
            $student->notify(new ScholarshipPosted(Scholarship::factory()->closed()->create(['title' => "Grant posted on day {$day}."])));
        }

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('(11 unread)');
        $response->assertSee('Grant posted on day 2.');
        $response->assertDontSee('Grant posted on day 1.');
    }

    public function test_a_student_without_notifications_sees_an_empty_bell(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertSee('No notifications yet. New scholarships you can apply to will show up here.');
        $response->assertDontSee('unread');
        $response->assertDontSee(route('notifications.read-all'));
    }

    public function test_the_bell_sits_only_in_the_dashboard_banner_not_in_the_navigation(): void
    {
        $student = User::factory()->create();
        $student->notify(new ScholarshipPosted(Scholarship::factory()->closed()->create(['title' => 'Posted Grant'])));

        $response = $this->actingAs($student)->get(route('scholarships.index'));

        $response->assertDontSee('Posted Grant');
        $response->assertDontSee('unread');
        $response->assertSee('aria-label="Open navigation"', false);
    }

    public function test_staff_have_no_bell(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'));

        $response->assertDontSee('No notifications yet.');
    }

    public function test_opening_a_notification_marks_it_read_and_opens_its_scholarship(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $student->notify(new ScholarshipPosted($scholarship));
        $notification = $student->notifications()->sole();

        $response = $this->actingAs($student)->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('scholarships.show', $scholarship));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_read_notification_leaves_the_bell_24_hours_after_it_was_read(): void
    {
        $student = User::factory()->create();
        $this->travelTo('2026-10-21 09:00:00');
        $student->notify(new ScholarshipPosted(Scholarship::factory()->closed()->create(['title' => 'Expired Grant'])));
        $student->unreadNotifications()->update(['read_at' => now()]);
        $this->travelTo('2026-10-21 10:00:00');
        $student->notify(new ScholarshipPosted(Scholarship::factory()->closed()->create(['title' => 'Recently Read Grant'])));
        $student->unreadNotifications()->update(['read_at' => now()]);
        $this->travelTo('2026-10-22 09:00:00');

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertSee('Recently Read Grant');
        $response->assertDontSee(', unread');
        $response->assertDontSee('Expired Grant');
    }

    public function test_a_notification_for_a_removed_scholarship_opens_the_scholarships_list(): void
    {
        $student = User::factory()->create();
        $scholarship = Scholarship::factory()->create();
        $student->notify(new ScholarshipPosted($scholarship));
        $scholarship->delete();

        $response = $this->actingAs($student)->post(route('notifications.read', $student->notifications()->sole()->id));

        $response->assertRedirect(route('scholarships.index'));
    }

    public function test_a_student_cannot_open_another_students_notification(): void
    {
        $owner = User::factory()->create();
        $owner->notify(new ScholarshipPosted(Scholarship::factory()->create()));
        $notification = $owner->notifications()->sole();

        $response = $this->actingAs(User::factory()->create())->post(route('notifications.read', $notification->id));

        $response->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_marking_all_as_read_clears_only_the_students_own_notifications(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        foreach ([$student, $student, $otherStudent] as $notifiable) {
            $notifiable->notify(new ScholarshipPosted(Scholarship::factory()->create()));
        }

        $response = $this->actingAs($student)->from(route('scholarships.index'))->post(route('notifications.read-all'));

        $response->assertRedirect(route('scholarships.index'));
        $this->assertSame(0, $student->unreadNotifications()->count());
        $this->assertSame(1, $otherStudent->unreadNotifications()->count());
    }

    public function test_staff_cannot_use_the_bell_routes(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('notifications.read-all'))
            ->assertForbidden();
    }
}
