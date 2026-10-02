<?php

namespace App\Enums;

/**
 * Whether a scholarship is taking applications and, if not, the first reason it isn't.
 */
enum ScholarshipAvailability: string
{
    case Open = 'open';
    case ClosedByOsas = 'closed_by_osas';
    case PastDeadline = 'past_deadline';
    case Full = 'full';
}
