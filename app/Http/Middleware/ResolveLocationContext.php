<?php

namespace App\Http\Middleware;

use App\Models\Location;
use App\Models\User;
use App\Support\CompanyContext;
use App\Support\LocationContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveLocationContext
{
    public function handle(Request $request, Closure $next, string $mode = 'required'): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED);

        $company = app(CompanyContext::class)->company();

        abort_unless(
            $company,
            Response::HTTP_FORBIDDEN,
            'A company context is required before resolving a location context.'
        );

        $location = $this->resolveLocation($request, $mode);

        if (! $location) {
            abort_unless(
                $mode === 'optional',
                Response::HTTP_FORBIDDEN,
                'No location context could be resolved for this request.'
            );

            return $next($request);
        }

        abort_unless(
            $location->company_id === $company->id,
            Response::HTTP_FORBIDDEN,
            'The requested location does not belong to the active company.'
        );

        abort_unless(
            $user->canAccessLocation($location, $company),
            Response::HTTP_FORBIDDEN,
            'You do not have access to this location.'
        );

        app(LocationContext::class)->setLocation($location);
        $request->attributes->set('active.location', $location);

        return $next($request);
    }

    protected function resolveLocation(Request $request, string $mode = 'required'): ?Location
    {
        $requestedId = $this->requestedLocationId($request);

        if ($requestedId) {
            return Location::query()->findOrFail($requestedId);
        }

        if ($mode === 'optional') {
            return null;
        }

        return null;
    }

    protected function requestedLocationId(Request $request): ?int
    {
        $id = $request->route('locationId')
            ?? $request->route('location_id')
            ?? $request->header('X-Location-Id')
            ?? $request->query('location_id');

        return $id ? (int) $id : null;
    }
}
