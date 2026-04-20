<?php

namespace App\Enums;

enum TokenFulfillmentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Fulfilled = 'fulfilled';
    case Failed = 'failed';
}

