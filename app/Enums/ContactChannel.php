<?php

namespace App\Enums;

enum ContactChannel: string
{
    case Sms = 'sms';
    case Email = 'email';
    case Call = 'call';
}

