<?php

namespace App\Support;

use App\Enums\ScholarshipType;

class ScholarshipPresets
{
    /**
     * The scholarships OSAS posts every year, which the Post Scholarship form
     * can start from. A preset fills in the title, type and course limit;
     * everything else, and any change to those, is up to OSAS.
     *
     * @var array<string, array{title: string, type: ScholarshipType, stem_courses_only: bool}>
     */
    public const ALL = [
        'tdp' => [
            'title' => 'Tulong Dunong Program (TDP)',
            'type' => ScholarshipType::Government,
            'stem_courses_only' => false,
        ],
        'tes' => [
            'title' => 'Tertiary Education Subsidy (TES)',
            'type' => ScholarshipType::Government,
            'stem_courses_only' => false,
        ],
        'dost' => [
            'title' => 'DOST Scholarship',
            'type' => ScholarshipType::Government,
            'stem_courses_only' => true,
        ],
    ];
}
