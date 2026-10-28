<?php

namespace Tests\Feature\View\Components;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PageSkeletonTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_page_a_student_opens_is_drafted_in_its_own_shape(): void
    {
        $student = User::factory()->named('Juan', 'Santos', 'Dela Cruz')->create();

        $response = $this->actingAs($student)->get(route('profile.edit'));

        $pages = $this->pageSkeletons($response);
        $this->assertSame(['shape' => 'panes', 'heading' => 'Welcome back, DELA CRUZ, JUAN SANTOS!', 'label' => 'Dashboard'], $this->drafted($pages, route('dashboard')));
        $this->assertSame(['shape' => 'record', 'hide' => ['action']], $this->drafted($pages, route('scholarships.republic-act-10931')));
        $this->assertSame(['shape' => 'leaf', 'hide' => ['fields']], $this->drafted($pages, route('scholarships.show', 7)));
        $this->assertSame('leaf', $this->drafted($pages, route('scholarships.apply', 7))['shape']);
        $this->assertSame('cards', $this->drafted($pages, route('scholarship-applications.index', ['page' => 2]))['shape']);
        $this->assertSame(['shape' => 'record'], $this->drafted($pages, route('scholarship-applications.show', 3)));
        $this->assertDraftsCarried($response, ['panes', 'record', 'leaf', 'cards', 'profile']);
    }

    public function test_each_page_an_admin_opens_is_drafted_in_its_own_shape(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $pages = $this->pageSkeletons($response);
        $this->assertSame('overview', $this->drafted($pages, route('admin.dashboard'))['shape']);
        $this->assertSame(['shape' => 'leaf', 'heading' => 'Post Scholarship'], array_diff_key($this->drafted($pages, route('admin.scholarships.create')), ['hide' => true]));
        $this->assertSame('record', $this->drafted($pages, route('admin.scholarships.show', 7))['shape']);
        $this->assertSame('leaf', $this->drafted($pages, route('admin.scholarships.edit', 7))['shape']);
        $this->assertSame('table', $this->drafted($pages, route('admin.fund-allocation.show', 7))['shape']);
        $this->assertSame(['shape' => 'table', 'heading' => 'Search Student'], array_diff_key($this->drafted($pages, route('admin.roster.index', ['search' => 'Reyes'])), ['hide' => true]));
        $this->assertSame('form', $this->drafted($pages, route('admin.students.edit', 5))['shape']);
        $this->assertDraftsCarried($response, ['overview', 'cards', 'leaf', 'record', 'table', 'form', 'profile']);
    }

    public function test_downloads_the_practice_tour_and_the_other_roles_pages_are_never_drafted(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        $adminPages = $this->pageSkeletons($this->actingAs($admin)->get(route('profile.edit')));
        $studentPages = $this->pageSkeletons($this->actingAs($student)->get(route('profile.edit')));

        $this->assertNull($this->drafted($adminPages, route('admin.scholarships.applications.report', 7)));
        $this->assertNull($this->drafted($adminPages, route('admin.fund-allocation.report', 7)));
        $this->assertNull($this->drafted($adminPages, route('scholarship-applications.documents.show', [7, 3])));
        $this->assertNull($this->drafted($adminPages, route('tutorial')));
        $this->assertNull($this->drafted($adminPages, route('dashboard')));
        $this->assertNull($this->drafted($studentPages, route('scholarships.required-documents.sample', [7, 3])));
        $this->assertNull($this->drafted($studentPages, route('scholarships.required-documents.sample.download', [7, 3])));
        $this->assertNull($this->drafted($studentPages, route('admin.dashboard')));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pageSkeletons(TestResponse $response): array
    {
        $this->assertSame(1, preg_match('#<script type="application/json" id="page-skeletons">(.*?)</script>#s', $response->getContent(), $match));

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * The draft the page's script would draw for a link to the URL, the way it
     * matches: the first entry whose path fits, `*` standing for one segment.
     *
     * @param  list<array<string, mixed>>  $pages
     * @return array<string, mixed>|null
     */
    private function drafted(array $pages, string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH);

        foreach ($pages as $page) {
            if (preg_match('#^'.str_replace('\*', '[^/]+', preg_quote($page['path'], '#')).'$#', $path) === 1) {
                return array_diff_key($page, ['path' => true]);
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $shapes
     */
    private function assertDraftsCarried(TestResponse $response, array $shapes): void
    {
        preg_match_all('#<template data-skeleton="([a-z-]+)">\s*<div data-draft#', $response->getContent(), $templates);

        $this->assertEqualsCanonicalizing($shapes, $templates[1]);
    }
}
