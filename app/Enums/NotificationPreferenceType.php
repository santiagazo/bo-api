<?php

namespace App\Enums;

enum NotificationPreferenceType: string
{
    case Touchpoints = 'touchpoints';
    case Jobs = 'jobs';
    case System = 'system';
}

