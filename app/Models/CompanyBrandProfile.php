<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBrandProfile extends BaseModel
{
    protected $fillable = [
        'logo_url',
        'icon_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'tagline',
        'university_name',
        'mission',
        'vision',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
