<?php

namespace App\Support;

class PersonName
{
    /**
     * Best-effort split of a single full name into its parts.
     *
     * The first token is taken as the given name, the last as the surname,
     * and anything between them as the middle name. That is a guess, and it
     * is wrong for the multi-word surnames that are common here — "Juan Dela
     * Cruz" yields a middle name of "Dela" and a surname of "Cruz". It exists
     * only to seed the columns for records that predate them; the parts are
     * stored so they can be corrected, never re-derived from the full name.
     *
     * @return array{first_name: ?string, middle_name: ?string, last_name: ?string}
     */
    public static function split(?string $fullName): array
    {
        $tokens = preg_split('/\s+/', trim((string) $fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return match (count($tokens)) {
            0 => ['first_name' => null, 'middle_name' => null, 'last_name' => null],
            1 => ['first_name' => $tokens[0], 'middle_name' => null, 'last_name' => null],
            2 => ['first_name' => $tokens[0], 'middle_name' => null, 'last_name' => $tokens[1]],
            default => [
                'first_name' => $tokens[0],
                'middle_name' => implode(' ', array_slice($tokens, 1, -1)),
                'last_name' => $tokens[count($tokens) - 1],
            ],
        };
    }

    /**
     * Join name parts into "first middle last", skipping the ones that are absent.
     */
    public static function assemble(?string $firstName, ?string $middleName, ?string $lastName): string
    {
        $parts = array_map(
            fn (?string $part): string => trim((string) $part),
            [$firstName, $middleName, $lastName],
        );

        return implode(' ', array_filter($parts, fn (string $part): bool => $part !== ''));
    }
}
