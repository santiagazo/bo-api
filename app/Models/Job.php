<?php

namespace App\Models;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Enums\LossType;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends BaseModel
{
    protected $fillable = [
        'company_id',
        'location_id',
        'customer_id',
        'property_address_id',
        'reference_id',
        'name',
        'status',
        'type',
        'property_type',
        'loss_type',
        'claim_number',
        'policy_number',
        'opened_at',
        'date_of_loss',
        'notes',
    ];

    protected $casts = [
        'status' => JobStatus::class,
        'type' => JobType::class,
        'property_type' => PropertyType::class,
        'loss_type' => LossType::class,
        'opened_at' => 'datetime',
        'date_of_loss' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function propertyAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'property_address_id');
    }
}
