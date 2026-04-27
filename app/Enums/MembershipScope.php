<?php

namespace App\Enums;

enum MembershipScope: string
{
    case Platform = 'platform';
    case Company = 'company';
    case Organization = 'organization';
}
