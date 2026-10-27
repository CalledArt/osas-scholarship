<?php

namespace Tests\Feature\View\Components;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_to_other_pages_are_prefetched_on_hover_and_never_prerendered(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('profile.edit'));

        $rules = $this->speculationRules($response);
        $this->assertArrayNotHasKey('prerender', $rules);
        $this->assertSame('moderate', $rules['prefetch'][0]['eagerness']);
        $this->assertTrue($this->prefetches($rules, route('dashboard')));
        $this->assertTrue($this->prefetches($rules, route('scholarships.show', 7)));
        $this->assertTrue($this->prefetches($rules, route('admin.roster.index', ['search' => 'Reyes'])));
    }

    public function test_reports_and_stored_files_are_never_prefetched(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $rules = $this->speculationRules($response);
        $this->assertFalse($this->prefetches($rules, route('admin.scholarships.applications.report', 7)));
        $this->assertFalse($this->prefetches($rules, route('admin.fund-allocation.report', 7)));
        $this->assertFalse($this->prefetches($rules, route('scholarship-applications.documents.show', [7, 3])));
        $this->assertFalse($this->prefetches($rules, route('scholarships.required-documents.sample', [7, 3])));
        $this->assertFalse($this->prefetches($rules, route('scholarships.required-documents.sample.download', [7, 3])));
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function speculationRules(TestResponse $response): array
    {
        $this->assertSame(1, preg_match('#<script type="speculationrules">(.*?)</script>#s', $response->getContent(), $match));

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Whether a prefetch rule's document-rule predicate selects a link to the URL.
     *
     * @param  array<string, list<array<string, mixed>>>  $rules
     */
    private function prefetches(array $rules, string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        foreach ($rules['prefetch'] as $rule) {
            if ($this->selects($rule['where'], $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate a predicate the way the browser does for these rules: `*` in a
     * pattern matches any run of characters, and the query string is ignored.
     *
     * @param  array<string, mixed>  $predicate
     */
    private function selects(array $predicate, string $path): bool
    {
        return match (array_key_first($predicate)) {
            'and' => collect($predicate['and'])->every(fn (array $part): bool => $this->selects($part, $path)),
            'or' => collect($predicate['or'])->contains(fn (array $part): bool => $this->selects($part, $path)),
            'not' => ! $this->selects($predicate['not'], $path),
            'href_matches' => preg_match('#^'.str_replace('\*', '.*', preg_quote($predicate['href_matches'], '#')).'$#', $path) === 1,
        };
    }
}
