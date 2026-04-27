<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationContact extends BaseModel
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'first_name',
        'last_name',
        'display_name',
        'title',
        'email',
        'phone',
        'mobile_phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
