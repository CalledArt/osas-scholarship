<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

/**
 * Guards the visual invariants recorded in DESIGN.md against the Laravel Breeze
 * defaults they replaced. These assert absences rather than exact utilities, so
 * ordinary restyling does not break them.
 */
class DesignSystemTest extends TestCase
{
    public function test_no_view_uses_the_breeze_indigo_focus_ring(): void
    {
        $offenders = [];

        /** @var SplFileInfo $file */
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (str_contains($file->getContents(), 'indigo')) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Focus rings are Citation Gold on a Seal Green fill, Seal Green on neutral controls, and the control\'s own hue on semantic controls. Indigo belongs to no part of this palette.');
    }

    public function test_button_components_do_not_uppercase_their_labels(): void
    {
        $offenders = [];

        foreach (['primary-button', 'secondary-button', 'danger-button'] as $component) {
            $contents = File::get(resource_path("views/components/{$component}.blade.php"));

            if (str_contains($contents, 'uppercase')) {
                $offenders[] = $component;
            }
        }

        $this->assertSame([], $offenders, 'Uppercase is reserved for table column headers and stat-tile captions. Button labels are sentence case.');
    }

    public function test_button_components_share_one_disabled_treatment(): void
    {
        foreach (['primary-button', 'secondary-button', 'danger-button'] as $component) {
            $contents = File::get(resource_path("views/components/{$component}.blade.php"));

            $this->assertStringContainsString('disabled:opacity-70', $contents, "The {$component} must dim to 70% when disabled; Breeze's 25% left the label unreadable.");
            $this->assertStringNotContainsString('disabled:opacity-25', $contents);
        }
    }
}
