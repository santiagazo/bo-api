<?php

namespace App\Models;

use App\Enums\JobStatus;
use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'location_id',
        'property_address_id',
        'assigned_user_id',
        'referred_by_organization_id',
        'contract_id',
        'reference_id',
        'status',
        'service_type',
        'lead_source',
        'description',
        'special_instructions',
        'estimated_value',
        'actual_value',
        'referral_fee_amount',
        'payment_status',
        'scheduled_date',
        'started_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'status' => JobStatus::class,
        'service_type' => ServiceType::class,
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function propertyAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'property_address_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function referredByOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'referred_by_organization_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_job')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function primaryCustomer(): Attribute
    {
        return Attribute::get(
            fn () => $this->customers()->wherePivot('is_primary', true)->first()
        );
    }

    public function notificationIntents(): HasMany
    {
        return $this->hasMany(NotificationIntent::class);
    }
}
