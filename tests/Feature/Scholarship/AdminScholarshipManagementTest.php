<?php

namespace Tests\Feature\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScholarshipManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_viewing_admin_scholarships(): void
    {
        $response = $this->get(route('admin.scholarships.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_is_forbidden_from_viewing_admin_scholarships(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('admin.scholarships.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_scholarship_with_required_documents(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
            'deadline' => now()->addMonth()->format('Y-m-d'),
            'required_documents' => [
                ['name' => '2x2 Picture', 'description' => 'White background'],
                ['name' => 'Certificate of Registration'],
            ],
        ]);

        $scholarship = Scholarship::sole();
        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $this->assertSame($admin->id, $scholarship->created_by);
        $this->assertSame(2, $scholarship->requiredDocuments()->count());
        $this->assertTrue($scholarship->requiredDocuments->pluck('name')->contains('2x2 Picture'));
    }

    public function test_student_cannot_create_scholarship(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->post(route('admin.scholarships.store'), [
            'title' => 'STEM Excellence Grant',
            'description' => 'For outstanding STEM students.',
            'slots' => 5,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('scholarships', 0);
    }

    public function test_creating_scholarship_requires_title_description_and_slots(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.scholarships.store'), []);

        $response->assertSessionHasErrors(['title', 'description', 'slots']);
    }

    public function test_admin_can_update_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create(['title' => 'Old Title']);

        $response = $this->actingAs($admin)->put(route('admin.scholarships.update', $scholarship), [
            'title' => 'New Title',
            'description' => $scholarship->description,
            'slots' => $scholarship->slots,
            'status' => 'closed',
        ]);

        $response->assertRedirect(route('admin.scholarships.show', $scholarship));
        $this->assertSame('New Title', $scholarship->fresh()->title);
        $this->assertSame('closed', $scholarship->fresh()->status->value);
    }

    public function test_admin_can_delete_scholarship(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();

        $response = $this->actingAs($admin)->delete(route('admin.scholarships.destroy', $scholarship));

        $response->assertRedirect(route('admin.scholarships.index'));
        $this->assertModelMissing($scholarship);
    }

    public function test_admin_can_add_and_remove_a_required_document(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->for($admin, 'postedBy')->create();

        $this->actingAs($admin)->post(route('admin.scholarships.required-documents.store', $scholarship), [
            'name' => 'Income Tax Return',
        ])->assertRedirect(route('admin.scholarships.show', $scholarship));

        $document = ScholarshipRequiredDocument::sole();
        $this->assertSame('Income Tax Return', $document->name);

        $this->actingAs($admin)
            ->delete(route('admin.scholarships.required-documents.destroy', [$scholarship, $document]))
            ->assertRedirect(route('admin.scholarships.show', $scholarship));

        $this->assertModelMissing($document);
    }
}
