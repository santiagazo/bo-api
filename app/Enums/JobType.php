<?php

namespace App\Enums;

enum JobType: string
{
    case Mitigation = 'mitigation';
    case Mold = 'mold';
    case Fire = 'fire';
    case Reconstruction = 'reconstruction';
    case Storm = 'storm';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Mitigation => 'Mitigation',
            self::Mold => 'Mold',
            self::Fire => 'Fire',
            self::Reconstruction => 'Reconstruction',
            self::Storm => 'Storm',
            self::Other => 'Other',
        };
    }
}
