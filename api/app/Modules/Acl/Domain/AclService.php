<?php

namespace App\Modules\Acl\Domain;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AclService
{
    public function check(User $user, Permission|string $permission, array $context = []): bool
    {
        $permissionValue = $permission instanceof Permission ? $permission->value : (string) $permission;

        if ($permissionValue === Permission::USER_UPDATE_SELF->value) {
            $targetId = $context['target_user_id'] ?? null;

            if ($targetId === null || (string) $targetId !== (string) $user->getKey()) {
                return false;
            }
        }

        return in_array($permissionValue, $this->getUserPermissions($user), true);
    }

    public function authorize(User $user, Permission|string $permission, array $context = []): void
    {
        if ($this->check($user, $permission, $context)) {
            return;
        }

        throw new AuthorizationException('Você não tem permissão para executar esta ação.');
    }

    public function getUserPermissions(User $user): array
    {
        $profile = $user->profile;

        if ($profile === null) {
            return [];
        }

        $permissions = $profile->permissions;

        if (!is_array($permissions)) {
            return [];
        }

        $unique = [];

        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                continue;
            }

            if (!in_array($permission, Permission::values(), true)) {
                continue;
            }

            if (!in_array($permission, $unique, true)) {
                $unique[] = $permission;
            }
        }

        return $unique;
    }

    public function availablePermissions(): array
    {
        return Permission::values();
    }
}
