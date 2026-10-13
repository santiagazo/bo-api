<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\CompanyPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\JobPolicy;
use App\Policies\LocationPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\CompanyContext;
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
        Company::class => CompanyPolicy::class,
        Customer::class => CustomerPolicy::class,
        Job::class => JobPolicy::class,
        Location::class => LocationPolicy::class,
        Organization::class => OrganizationPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Support\CompanyContext::class);
        $this->app->singleton(\App\Support\LocationContext::class);
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

        Gate::before(function (User $user, string $ability) {
            if (app(CompanyContext::class)->company()) {
                return null;
            }

            if (
                $user->hasPlatformAccess()
                && $user->platformMembership()?->hasPermissionTo('*:*')
            ) {
                return true;
            }

            return null; // Fall through to policy check
        });
    }
}
