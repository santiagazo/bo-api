<?php

namespace App\Models;

use App\Enums\LocationColor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends BaseModel
{
    public const DEFAULT_COLORS = [
        LocationColor::Blue,
        LocationColor::Red,
        LocationColor::Green,
        LocationColor::Orange,
        LocationColor::Violet,
        LocationColor::Teal,
        LocationColor::Pink,
        LocationColor::Lime,
        LocationColor::Slate,
        LocationColor::Gold,
    ];

    protected $fillable = [
        'company_id',
        'address_id',
        'name',
        'code',
        'sort_order',
        'color',
        'phone',
        'phone_country_code',
        'email',
        'status',
        'timezone',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getColorAttribute(?string $value): ?string
    {
        return LocationColor::normalize($value);
    }

    public function setColorAttribute(LocationColor|string|null $value): void
    {
        $this->attributes['color'] = LocationColor::normalize($value);
    }

    public function resolvedColor(): ?LocationColor
    {
        return LocationColor::resolve($this->getRawOriginal('color'));
    }

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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
}
