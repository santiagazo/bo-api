<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\User;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED);

        $organization = $this->resolveOrganization($request, $user);

        abort_if(
            ! $organization,
            Response::HTTP_FORBIDDEN,
            'No organization context could be resolved for this request.'
        );

        abort_unless(
            $user->canAccessOrganization($organization),
            Response::HTTP_FORBIDDEN,
            'You do not have access to this organization.'
        );

        app(TenantContext::class)->setOrganization($organization);
        $request->attributes->set('tenant.organization', $organization);

        return $next($request);
    }

    protected function resolveOrganization(Request $request, User $user): ?Organization
    {
        $requestedId = $this->requestedOrganizationId($request);

        if ($requestedId) {
            return Organization::query()->findOrFail($requestedId);
        }

        if ($user->organization_id) {
            return $user->organization()->first();
        }

        return null;
    }

    protected function requestedOrganizationId(Request $request): ?int
    {
        $id = $request->route('organizationId')
            ?? $request->route('organization_id')
            ?? $request->header('X-Organization-Id')
            ?? $request->header('Organization-Id')
            ?? $request->query('organization_id');

        return $id ? (int) $id : null;
    }
}
