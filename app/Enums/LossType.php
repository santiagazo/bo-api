<?php

namespace App\Enums;

enum LossType: string
{
    case Water = 'water';
    case Mold = 'mold';
    case Fire = 'fire';
    case Storm = 'storm';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Water => 'Water',
            self::Mold => 'Mold',
            self::Fire => 'Fire',
            self::Storm => 'Storm',
            self::Other => 'Other',
        };
    }
}
