<?php

namespace App\Enums;

enum CampaignType: string
{
    case Raffle = 'raffle';
    case Contest = 'contest';
    case Points = 'points';
}

