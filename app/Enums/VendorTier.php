<?php

namespace App\Enums;

enum VendorTier: string
{
    case Prospect = 'prospect';
    case Partner = 'partner';
    case PreferredPartner = 'preferred_partner';
    case ReferralTree = 'referral_tree';
    case Elite = 'elite';
}

