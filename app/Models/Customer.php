<?php

namespace App\Models;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends BaseModel
{
    protected $fillable = [
        'company_id',
        'type',
        'name',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'type' => CustomerType::class,
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class)->orderByDesc('is_primary')->orderBy('name');
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(CustomerContact::class)
            ->where('is_primary', true)
            ->latestOfMany();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function primaryAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)
            ->where('is_primary', true)
            ->latestOfMany();
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)
            ->where('type', 'billing')
            ->latestOfMany();
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class)->orderByDesc('opened_at')->orderByDesc('id');
    }

    public function primaryContactName(): Attribute
    {
        return Attribute::get(fn () => $this->primaryContact?->name);
    }
}
