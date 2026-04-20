<?php

namespace App\Enums;

enum VendorStatus: string
{
    case TrialPeriod = 'trial_period';
    case Approved = 'approved';
    case Flagged = 'flagged';
    case Paused = 'paused';
    case DoNotContact = 'do_not_contact';
}

