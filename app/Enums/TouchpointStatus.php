<?php

namespace App\Enums;

enum TouchpointStatus: string
{
    case Scheduled = 'scheduled';
    case Sent = 'sent';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}

