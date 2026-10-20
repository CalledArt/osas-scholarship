<?php

namespace App\Support;

class CvsuPrograms
{
    /**
     * The undergraduate programs a student can register under, by college.
     *
     * Names are the current ones (BA Journalism, not BA Mass Communication),
     * and BS Industrial Technology is listed once per major. The College of
     * Education's science high school and elementary programs are left out,
     * since their pupils are not tertiary students.
     *
     * @var array<string, list<string>>
     */
    public const BY_COLLEGE = [
        'College of Agriculture, Food, Environment and Natural Resources (CAFENR)' => [
            'Bachelor of Agricultural Entrepreneurship',
            'BS Agriculture',
            'BS Environmental Science',
            'BS Food Technology',
        ],
        'College of Arts and Sciences (CAS)' => [
            'BA English Language Studies',
            'BA Journalism',
            'BA Political Science',
            'BS Applied Mathematics',
            'BS Biology',
            'BS Psychology',
            'BS Social Work',
        ],
        'College of Criminal Justice (CCJ)' => [
            'BS Criminology',
            'BS Industrial Security Management',
        ],
        'College of Economics, Management, and Development Studies (CEMDS)' => [
            'BS Accountancy',
            'BS Business Management',
            'BS Development Management',
            'BS Economics',
            'BS International Studies',
            'BS Office Administration',
        ],
        'College of Education (CED)' => [
            'Bachelor of Early Childhood Education',
            'Bachelor of Elementary Education',
            'Bachelor of Secondary Education',
            'Bachelor of Special Needs Education',
            'Bachelor of Technology and Livelihood Education',
            'BS Hospitality Management',
            'BS Tourism Management',
            'Teacher Certificate Program',
        ],
        'College of Engineering and Information Technology (CEIT)' => [
            'BS Agricultural and Biosystems Engineering',
            'BS Architecture',
            'BS Civil Engineering',
            'BS Computer Engineering',
            'BS Computer Science',
            'BS Electrical Engineering',
            'BS Electronics Engineering',
            'BS Industrial Engineering',
            'BS Industrial Technology major in Automotive Technology',
            'BS Industrial Technology major in Electrical Technology',
            'BS Industrial Technology major in Electronics Technology',
            'BS Information Technology',
        ],
        'College of Nursing (CON)' => [
            'BS Medical Technology',
            'BS Midwifery',
            'BS Nursing',
            'Diploma in Midwifery',
        ],
        'College of Sports, Physical Education and Recreation (CSPEAR)' => [
            'Bachelor of Physical Education',
            'Bachelor of Exercise and Sports Sciences',
        ],
        'College of Veterinary Medicine and Biomedical Sciences (CVMBS)' => [
            'Doctor of Veterinary Medicine',
        ],
    ];

    /**
     * The programs above that DOST-SEI lists as S&T priority fields, which
     * are the only ones a STEM-courses-only scholarship accepts.
     *
     * Taken from the "S&T Undergraduate Scholarship Priority Fields" in the
     * DOST-SEI 2026 Undergraduate Scholarship brochure, keeping only exact
     * name matches. Near matches are left out: Agricultural Entrepreneurship
     * (DOST lists Agribusiness Management and Entrepreneurship), Exercise and
     * Sports Sciences (DOST lists Sports Science), and the Education degrees,
     * which DOST accepts only in science, mathematics or ICT majors that the
     * list above does not record.
     *
     * @var list<string>
     */
    public const STEM = [
        'BS Agriculture',
        'BS Environmental Science',
        'BS Food Technology',
        'BS Applied Mathematics',
        'BS Biology',
        'BS Psychology',
        'BS Agricultural and Biosystems Engineering',
        'BS Architecture',
        'BS Civil Engineering',
        'BS Computer Engineering',
        'BS Computer Science',
        'BS Electrical Engineering',
        'BS Electronics Engineering',
        'BS Industrial Engineering',
        'BS Information Technology',
        'BS Medical Technology',
        'Doctor of Veterinary Medicine',
    ];

    /**
     * Every program, across all colleges.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_merge(...array_values(self::BY_COLLEGE));
    }

    /**
     * Whether the course is one of the STEM programs. A course saved before
     * the program list existed matches only once OSAS recodes it.
     */
    public static function isStem(?string $course): bool
    {
        return in_array($course, self::STEM, true);
    }
}
