<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends BaseModel
{
    protected $fillable = [
        'customer_id',
        'type',
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'country',
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
}
