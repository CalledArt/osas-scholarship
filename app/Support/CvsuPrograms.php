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
     * The short form of each program, as the fund allocation sheet prints it.
     *
     * Each abbreviation is unique, so Agriculture keeps BSA and Accountancy
     * is BSAcc, and the Industrial Technology majors are BSIndT so they never
     * read as BSIT (Information Technology).
     *
     * @var array<string, string>
     */
    public const ABBREVIATIONS = [
        'Bachelor of Agricultural Entrepreneurship' => 'BAE',
        'BS Agriculture' => 'BSA',
        'BS Environmental Science' => 'BSES',
        'BS Food Technology' => 'BSFT',
        'BA English Language Studies' => 'BAELS',
        'BA Journalism' => 'BAJ',
        'BA Political Science' => 'BAPS',
        'BS Applied Mathematics' => 'BSAM',
        'BS Biology' => 'BSBio',
        'BS Psychology' => 'BSPsych',
        'BS Social Work' => 'BSSW',
        'BS Criminology' => 'BSCrim',
        'BS Industrial Security Management' => 'BSISM',
        'BS Accountancy' => 'BSAcc',
        'BS Business Management' => 'BSBM',
        'BS Development Management' => 'BSDM',
        'BS Economics' => 'BSEcon',
        'BS International Studies' => 'BSIS',
        'BS Office Administration' => 'BSOA',
        'Bachelor of Early Childhood Education' => 'BECEd',
        'Bachelor of Elementary Education' => 'BEEd',
        'Bachelor of Secondary Education' => 'BSEd',
        'Bachelor of Special Needs Education' => 'BSNEd',
        'Bachelor of Technology and Livelihood Education' => 'BTLEd',
        'BS Hospitality Management' => 'BSHM',
        'BS Tourism Management' => 'BSTM',
        'Teacher Certificate Program' => 'TCP',
        'BS Agricultural and Biosystems Engineering' => 'BSABE',
        'BS Architecture' => 'BSArch',
        'BS Civil Engineering' => 'BSCE',
        'BS Computer Engineering' => 'BSCpE',
        'BS Computer Science' => 'BSCS',
        'BS Electrical Engineering' => 'BSEE',
        'BS Electronics Engineering' => 'BSECE',
        'BS Industrial Engineering' => 'BSIE',
        'BS Industrial Technology major in Automotive Technology' => 'BSIndT-AT',
        'BS Industrial Technology major in Electrical Technology' => 'BSIndT-ELT',
        'BS Industrial Technology major in Electronics Technology' => 'BSIndT-ECT',
        'BS Information Technology' => 'BSIT',
        'BS Medical Technology' => 'BSMT',
        'BS Midwifery' => 'BSM',
        'BS Nursing' => 'BSN',
        'Diploma in Midwifery' => 'DM',
        'Bachelor of Physical Education' => 'BPEd',
        'Bachelor of Exercise and Sports Sciences' => 'BESS',
        'Doctor of Veterinary Medicine' => 'DVM',
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

    /**
     * The course's abbreviation. A course saved before the program list
     * existed has none, so it is returned as stored.
     */
    public static function abbreviation(?string $course): ?string
    {
        return $course === null ? null : (self::ABBREVIATIONS[$course] ?? $course);
    }
}
