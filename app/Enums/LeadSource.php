<?php

namespace App\Enums;

enum LeadSource: string
{
    case VendorReferral = 'vendor_referral';
    case Website = 'website';
    case DirectCall = 'direct_call';
    case SocialMedia = 'social_media';
    case LeadGenEverconnect = 'lead_gen_everconnect';
    case LeadGenServiceDirect = 'lead_gen_service_direct';
    case LeadGenInquirly = 'lead_gen_inquirly';
}

