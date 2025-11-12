<?php

namespace App\Modules\Acl\Http\Middleware;

use App\Modules\Acl\Domain\AclService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(private readonly AclService $aclService)
    {
    }

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_FORBIDDEN, 'Usuário não autenticado.');
        }

        $normalizedPermissions = $this->normalizePermissions($permissions);
        $context = $this->buildContext($request);

        foreach ($normalizedPermissions as $permission) {
            if ($this->aclService->check($user, $permission, $context)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN, 'Você não tem permissão para executar esta ação.');
    }

    private function normalizePermissions(array $permissions): array
    {
        $normalized = [];

        foreach ($permissions as $permission) {
            $chunks = preg_split('/[|,]/', (string) $permission) ?: [];

            foreach ($chunks as $chunk) {
                $chunk = trim($chunk);

                if ($chunk === '') {
                    continue;
                }

                if (!in_array($chunk, $normalized, true)) {
                    $normalized[] = $chunk;
                }
            }
        }

        return $normalized;
    }

    private function buildContext(Request $request): array
    {
        $context = [];
        $targetUserId = $request->route('user');

        if ($targetUserId !== null) {
            $context['target_user_id'] = $targetUserId;
        }

        return $context;
    }
}
