<?php

namespace App\Enums;

enum ScholarshipStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    /**
     * The grant period is over: approved students no longer hold it, so it
     * stops counting toward their scholarship limit.
     */
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Ended => 'Ended',
        };
    }
}
