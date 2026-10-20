<?php

namespace App\Enums;

enum ScholarshipType: string
{
    case Government = 'government';
    case Academic = 'academic';

    public function label(): string
    {
        return match ($this) {
            self::Government => 'Government',
            self::Academic => 'Academic',
        };
    }
}
