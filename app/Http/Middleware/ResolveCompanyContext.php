<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompanyContext
{
    public function handle(Request $request, Closure $next, string $mode = 'required'): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_unless($user, Response::HTTP_UNAUTHORIZED);

        $company = $this->resolveCompany($request, $user, $mode);

        if (! $company) {
            abort_unless(
                $mode === 'optional',
                Response::HTTP_FORBIDDEN,
                'No company context could be resolved for this request.'
            );

            return $next($request);
        }

        abort_unless(
            $user->canAccessCompany($company),
            Response::HTTP_FORBIDDEN,
            'You do not have access to this company.'
        );

        app(CompanyContext::class)->setCompany($company);
        $request->attributes->set('active.company', $company);

        return $next($request);
    }

    protected function resolveCompany(Request $request, User $user, string $mode = 'required'): ?Company
    {
        $requestedId = $this->requestedCompanyId($request);

        if ($requestedId) {
            return Company::query()->findOrFail($requestedId);
        }

        if ($mode === 'optional') {
            return null;
        }

        $primaryCompanyId = $user->primaryCompanyId();

        if ($primaryCompanyId) {
            return Company::query()->findOrFail($primaryCompanyId);
        }

        return null;
    }

    protected function requestedCompanyId(Request $request): ?int
    {
        $id = $request->route('companyId')
            ?? $request->route('company_id')
            ?? $request->header('X-Company-Id')
            ?? $request->header('Company-Id')
            ?? $request->query('company_id');

        return $id ? (int) $id : null;
    }
}
