<?php

namespace App\Enums;

enum ServiceType: string
{
    case Water = 'water';
    case Fire = 'fire';
    case Mold = 'mold';
    case Storm = 'storm';
}
