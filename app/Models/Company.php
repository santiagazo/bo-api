<?php

namespace App\Models;

use App\Enums\MembershipScope;
use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends BaseModel
{
    protected $with = ['brandProfile'];

    protected $fillable = [
        'name',
        'legal_name',
        'slug',
        'status',
        'primary_email',
        'primary_phone',
        'phone_country_code',
        'website_url',
        'timezone',
        'currency_code',
        'default_locale',
        'address_id',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function brandProfile(): HasOne
    {
        return $this->hasOne(CompanyBrandProfile::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships', 'company_id', 'user_id')
            ->wherePivot('scope_type', MembershipScope::Company->value)
            ->wherePivot('status', MembershipStatus::Active->value)
            ->wherePivotNull('deleted_at')
            ->distinct();
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
