<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContactEmailAddress extends BaseModel
{
    protected $fillable = [
        'customer_contact_id',
        'type',
        'email',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customerContact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class);
    }
}
