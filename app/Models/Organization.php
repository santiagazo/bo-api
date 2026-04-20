<?php

namespace App\Models;

use App\Enums\OrganizationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'phone',
        'email',
        'phone_country_code',
        'address_id',
        'referral_fee_percentage',
    ];

    protected $casts = [
        'type' => OrganizationType::class,
        'referral_fee_percentage' => 'decimal:2',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function referredJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'referred_by_organization_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function userGroups(): HasMany
    {
        return $this->hasMany(UserGroup::class);
    }
}
