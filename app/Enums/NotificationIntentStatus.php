<?php

namespace App\Enums;

enum NotificationIntentStatus: string
{
    case Queued = 'queued';
    case Attempted = 'attempted';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Acknowledged = 'acknowledged';
}
