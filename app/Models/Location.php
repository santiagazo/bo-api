<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends BaseModel
{
    protected $fillable = [
        'company_id',
        'address_id',
        'name',
        'code',
        'phone',
        'phone_country_code',
        'email',
        'status',
        'timezone',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
