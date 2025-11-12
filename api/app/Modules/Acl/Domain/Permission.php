<?php

namespace App\Modules\Acl\Domain;

enum Permission: string
{
    case USER_VIEW = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';
    case USER_UPDATE_SELF = 'user.update_self';

    public static function values(): array
    {
        return array_map(static fn (self $permission) => $permission->value, self::cases());
    }
}
