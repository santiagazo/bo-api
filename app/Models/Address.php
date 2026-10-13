<?php

namespace App\Models;

class Address extends BaseModel
{
    protected $fillable = [
        'name',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
    ];
}
