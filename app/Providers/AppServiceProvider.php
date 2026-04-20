<?php

namespace App\Providers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\ContractPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\JobPolicy;
use App\Policies\LocationPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\UserGroupPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Contract::class => ContractPolicy::class,
        Customer::class => CustomerPolicy::class,
        Job::class => JobPolicy::class,
        Location::class => LocationPolicy::class,
        Organization::class => OrganizationPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
        UserGroup::class => UserGroupPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Grant all abilities to system admins (users with *:* permission)
        // This runs before any policy check
        Gate::before(function (User $user, string $ability) {
            if ($user->can('*:*')) {
                return true;
            }

            return null; // Fall through to policy check
        });
    }
}
