<?php

namespace App\Modules\Acl\Domain;

enum Permission: string
{
    case USER_VIEW = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';
    case USER_UPDATE_SELF = 'user.update_self';
    case PROFILE_VIEW = 'profile.view';
    case PROFILE_CREATE = 'profile.create';
    case PROFILE_UPDATE = 'profile.update';
    case PROFILE_DELETE = 'profile.delete';
    case CATEGORY_VIEW = 'category.view';
    case CATEGORY_CREATE = 'category.create';
    case CATEGORY_UPDATE = 'category.update';
    case CATEGORY_DELETE = 'category.delete';

    public static function values(): array
    {
        return array_map(static fn (self $permission) => $permission->value, self::cases());
    }
}
