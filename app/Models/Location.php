<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'address_id',
        'name',
        'phone',
        'phone_country_code',
        'email',
        'is_active',
        'is_default',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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
