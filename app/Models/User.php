<?php

namespace App\Models;

use App\Enums\OrganizationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPermissions, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'phone',
        'phone_country_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsTo<Organization,User>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<Job,User>
     */
    public function assignedJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'assigned_user_id');
    }

    /**
     * @return HasMany<NotificationPreference,User>
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class)
            ->withTimestamps();
    }

    public function scopeWithRelations($query)
    {
        return $query->with(['organization', 'roles.permissions']);
    }

    public function hasSystemAccess(): bool
    {
        if (! $this->belongsToGlobalOrganization()) {
            return false;
        }

        return $this->getAllPermissions()
            ->pluck('name')
            ->intersect(['*:*', 'access:system'])
            ->isNotEmpty();
    }

    public function belongsToGlobalOrganization(): bool
    {
        if (! $this->organization_id) {
            return false;
        }

        if ($this->relationLoaded('organization')) {
            return $this->organization?->type === OrganizationType::Global;
        }

        return $this->organization()
            ->where('type', OrganizationType::Global)
            ->exists();
    }

    public function canAccessOrganization(Organization|int|null $organization): bool
    {
        if (! $organization) {
            return $this->hasSystemAccess();
        }

        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return $this->hasSystemAccess() || (int) $this->organization_id === (int) $organizationId;
    }
}
