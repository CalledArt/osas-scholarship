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
}
