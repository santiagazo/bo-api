<?php

namespace App\Enums;

enum JobStatus: string
{
    case Lead = 'lead';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Complete = 'complete';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::Active => 'Active',
            self::OnHold => 'On Hold',
            self::Complete => 'Complete',
            self::Cancelled => 'Cancelled',
        };
    }
}
