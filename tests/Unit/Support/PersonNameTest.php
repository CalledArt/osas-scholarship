<?php

namespace Tests\Unit\Support;

use App\Support\PersonName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PersonNameTest extends TestCase
{
    /**
     * @return array<string, array{0: ?string, 1: array{first_name: ?string, middle_name: ?string, last_name: ?string}}>
     */
    public static function fullNames(): array
    {
        return [
            'three tokens' => [
                'Maria Santos Reyes',
                ['first_name' => 'Maria', 'middle_name' => 'Santos', 'last_name' => 'Reyes'],
            ],
            'two tokens leave no middle name' => [
                'Maria Reyes',
                ['first_name' => 'Maria', 'middle_name' => null, 'last_name' => 'Reyes'],
            ],
            'a single token is only a first name' => [
                'Maria',
                ['first_name' => 'Maria', 'middle_name' => null, 'last_name' => null],
            ],
            'four tokens widen the middle name' => [
                'Ana Maria Lopez Reyes',
                ['first_name' => 'Ana', 'middle_name' => 'Maria Lopez', 'last_name' => 'Reyes'],
            ],
            'surrounding and repeated whitespace is collapsed' => [
                "  Maria \t Santos   Reyes  ",
                ['first_name' => 'Maria', 'middle_name' => 'Santos', 'last_name' => 'Reyes'],
            ],
            'an empty name yields no parts' => [
                '',
                ['first_name' => null, 'middle_name' => null, 'last_name' => null],
            ],
            'null yields no parts' => [
                null,
                ['first_name' => null, 'middle_name' => null, 'last_name' => null],
            ],
        ];
    }

    #[DataProvider('fullNames')]
    public function test_splits_a_full_name_into_its_parts(?string $fullName, array $expected): void
    {
        $this->assertSame($expected, PersonName::split($fullName));
    }

    /**
     * The split cannot know that "Dela Cruz" is one surname. This documents the
     * wrong answer it gives so the backfill's known limitation stays visible:
     * a student corrects it on the profile screen, and nothing re-derives it.
     */
    public function test_splits_a_multi_word_surname_incorrectly(): void
    {
        $parts = PersonName::split('Juan Dela Cruz');

        $this->assertSame('Dela', $parts['middle_name']);
        $this->assertSame('Cruz', $parts['last_name']);
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string, 2: ?string, 3: string}>
     */
    public static function nameParts(): array
    {
        return [
            'all three parts' => ['Maria', 'Santos', 'Reyes', 'Maria Santos Reyes'],
            'a missing middle name closes the gap' => ['Maria', null, 'Reyes', 'Maria Reyes'],
            'a blank middle name closes the gap' => ['Maria', '   ', 'Reyes', 'Maria Reyes'],
            'a multi-word surname is kept whole' => ['Juan', null, 'Dela Cruz', 'Juan Dela Cruz'],
            'parts are trimmed' => ['  Maria  ', null, '  Reyes  ', 'Maria Reyes'],
            'no parts at all' => [null, null, null, ''],
        ];
    }

    #[DataProvider('nameParts')]
    public function test_assembles_parts_into_a_full_name(?string $firstName, ?string $middleName, ?string $lastName, string $expected): void
    {
        $this->assertSame($expected, PersonName::assemble($firstName, $middleName, $lastName));
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string, 2: ?string, 3: string}>
     */
    public static function surnameFirstNames(): array
    {
        return [
            'all three parts' => ['Juan', 'Santos', 'Dela Cruz', 'Dela Cruz, Juan Santos'],
            'a missing middle name' => ['Juan', null, 'Dela Cruz', 'Dela Cruz, Juan'],
            'no surname leaves the given names' => ['Juan', 'Santos', '  ', 'Juan Santos'],
            'only a surname' => [null, null, 'Dela Cruz', 'Dela Cruz'],
            'no parts at all' => [null, null, null, ''],
        ];
    }

    #[DataProvider('surnameFirstNames')]
    public function test_assembles_parts_surname_first(?string $firstName, ?string $middleName, ?string $lastName, string $expected): void
    {
        $this->assertSame($expected, PersonName::surnameFirst($firstName, $middleName, $lastName));
    }
}
