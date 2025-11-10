<?php

namespace App\Modules\Company\Http;

use App\Models\User;
use App\Modules\Company\Domain\CompanyRegistry;
use App\Modules\Company\Domain\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeCompany
{
    public function __construct(private CompanyRegistry $companyRegistry)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $company = $this->resolveCompany($request);

        if ($company instanceof Company) {
            $this->companyRegistry->registerCompany($company);
        }

        return $next($request);
    }

    private function resolveCompany(Request $request): ?Company
    {
        $user = $request->user();

        if ($user instanceof User) {
            $company = $user->company;

            if ($company instanceof Company) {
                return $company;
            }
        }

        $referer = $request->headers->get('referer');

        if (is_string($referer) && $referer !== '') {
            $normalizedDomain = $this->normalizeDomain($referer);

            if ($normalizedDomain !== '') {
                return Company::query()
                    ->where('domain', $normalizedDomain)
                    ->first();
            }
        }

        return null;
    }

    private function normalizeDomain(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            $value = $host;
        } else {
            $value = preg_replace('#^https?://#i', '', $value);
        }

        $value = preg_replace('#^www\.#i', '', $value);
        $value = rtrim($value, '/');

        return strtolower($value);
    }
}

