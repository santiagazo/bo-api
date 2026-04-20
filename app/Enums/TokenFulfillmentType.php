<?php

namespace App\Enums;

enum TokenFulfillmentType: string
{
    case Deliver = 'deliver';
    case Ship = 'ship';
    case Pickup = 'pickup';
    case Digital = 'digital';
}

