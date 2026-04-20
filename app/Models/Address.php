<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends BaseModel
{
    use HasFactory, SoftDeletes;

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
