<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends BaseModel
{
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'phone_country_code',
        'address_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'customer_job')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }
}
