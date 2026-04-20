<?php

namespace App\Enums;

enum ParticipationScope: string
{
    case Organization = 'organization';
    case Location = 'location';
    case Individual = 'individual';
}

