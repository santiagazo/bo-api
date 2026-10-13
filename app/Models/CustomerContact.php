<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerContact extends BaseModel
{
    protected $fillable = [
        'customer_id',
        'name',
        'title',
        'notes',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(CustomerContactPhoneNumber::class)
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }

    public function primaryPhoneNumber(): HasOne
    {
        return $this->hasOne(CustomerContactPhoneNumber::class)
            ->where('is_primary', true)
            ->latestOfMany();
    }

    public function emailAddresses(): HasMany
    {
        return $this->hasMany(CustomerContactEmailAddress::class)
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }

    public function primaryEmailAddress(): HasOne
    {
        return $this->hasOne(CustomerContactEmailAddress::class)
            ->where('is_primary', true)
            ->latestOfMany();
    }
}
