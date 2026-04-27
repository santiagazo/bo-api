<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends BaseModel
{
    protected $fillable = [
        'company_id',
        'display_name',
        'legal_name',
        'status',
        'website_url',
        'primary_email',
        'primary_phone',
        'phone_country_code',
        'address_id',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function typeAssignments(): HasMany
    {
        return $this->hasMany(OrganizationTypeAssignment::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(OrganizationContact::class);
    }
}
