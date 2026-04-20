<?php

namespace App\Enums;

enum OrganizationType: string
{
    case Global = 'global';
    case Vendor = 'vendor';
    case Contractor = 'contractor';
    case Internal = 'internal';
}
