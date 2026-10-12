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
     * Every program, across all colleges.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_merge(...array_values(self::BY_COLLEGE));
    }
}
