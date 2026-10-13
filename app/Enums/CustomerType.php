<?php

namespace App\Enums;

enum CustomerType: string
{
    case Person = 'person';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Person => 'Person',
            self::Business => 'Business',
        };
    }
}
