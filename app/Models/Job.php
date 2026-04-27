<?php

namespace App\Models;

use App\Enums\JobStatus;
use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Job extends BaseModel
{
    protected $fillable = [
        'company_id',
        'location_id',
        'property_address_id',
        'assigned_user_id',
        'referred_by_organization_id',
        'reference_id',
        'job_name',
        'status',
        'service_type',
        'loss_type',
        'billing_status',
        'bill_to_type',
        'claim_number',
        'policy_number',
        'lead_source',
        'description',
        'special_instructions',
        'actual_value',
        'payment_status',
        'date_of_loss',
        'mitigation_started_at',
        'mitigation_completed_at',
        'cancelled_at',
        'story_summary',
        'adjuster_summary',
        'notes',
    ];

    protected $casts = [
        'status' => JobStatus::class,
        'service_type' => ServiceType::class,
        'date_of_loss' => 'date',
        'mitigation_started_at' => 'datetime',
        'mitigation_completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
}
