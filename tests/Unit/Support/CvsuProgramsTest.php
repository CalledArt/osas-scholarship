<?php

namespace Tests\Unit\Support;

use App\Support\CvsuPrograms;
use PHPUnit\Framework\TestCase;

class CvsuProgramsTest extends TestCase
{
    public function test_every_stem_course_is_a_program_students_can_register_under(): void
    {
        $unregistrable = array_diff(CvsuPrograms::STEM, CvsuPrograms::all());

        $this->assertSame([], array_values($unregistrable));
    }

    public function test_every_program_has_its_own_abbreviation(): void
    {
        $this->assertEqualsCanonicalizing(CvsuPrograms::all(), array_keys(CvsuPrograms::ABBREVIATIONS));
        $this->assertSame(array_unique(CvsuPrograms::ABBREVIATIONS), CvsuPrograms::ABBREVIATIONS);
    }

    public function test_a_course_outside_the_program_list_keeps_its_stored_name(): void
    {
        $this->assertSame('BSCS', CvsuPrograms::abbreviation('BS Computer Science'));
        $this->assertSame('BS Fisheries', CvsuPrograms::abbreviation('BS Fisheries'));
        $this->assertNull(CvsuPrograms::abbreviation(null));
    }
}
