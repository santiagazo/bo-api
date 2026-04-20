<?php

namespace App\Enums;

enum NotificationIntentType: string
{
    case Sms = 'sms';
    case Email = 'email';
    case Call = 'call';
    case Push = 'push';
}
