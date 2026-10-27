<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';

    /**
     * The student has stopped studying for now: free tuition under RA 10931
     * pauses, and they can neither apply to nor be granted a scholarship,
     * until OSAS marks them active again.
     */
    case LeaveOfAbsence = 'leave_of_absence';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::LeaveOfAbsence => 'Leave of Absence (LOA)',
        };
    }
}
