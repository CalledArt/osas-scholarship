<?php

namespace App\Support;

use App\Enums\ScholarshipType;

class ScholarshipPresets
{
    /**
     * The scholarships OSAS posts every year, which the Post Scholarship form
     * can start from. A preset fills in the title and type, which OSAS can
     * still change. The course limit is not a field: only the DOST preset
     * limits a scholarship to STEM courses, and nothing else can.
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

    /**
     * Whether a scholarship posted from the given preset is open only to STEM courses.
     *
     * Decided by the preset, never by the title, which OSAS may reword.
     */
    public static function limitsToStemCourses(?string $preset): bool
    {
        return $preset !== null && (self::ALL[$preset]['stem_courses_only'] ?? false);
    }
}
