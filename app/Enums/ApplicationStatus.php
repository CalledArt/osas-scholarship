<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * OSAS put the student on leave of absence while the application was
     * pending, so it gave up its slot for another student to take. It counts
     * toward no limit.
     */
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Voided => 'Voided',
        };
    }
}
