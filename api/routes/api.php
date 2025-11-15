<?php

use App\Modules\Acl\Http\Controllers\ProfileController;
use App\Modules\Catalog\Attribute\Http\Controllers\AttributeController;
use App\Modules\Catalog\Category\Http\Controllers\CategoryController;
use App\Modules\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('users', UserController::class)
        ->middleware([
            'index' => 'permission:user.view',
            'show' => 'permission:user.view',
            'store' => 'permission:user.create',
            'update' => 'permission:user.update|user.update_self',
            'destroy' => 'permission:user.delete',
        ]);

    Route::apiResource('profiles', ProfileController::class)
        ->middleware([
            'index' => 'permission:profile.view',
            'show' => 'permission:profile.view',
            'store' => 'permission:profile.create',
            'update' => 'permission:profile.update',
            'destroy' => 'permission:profile.delete',
        ]);

    Route::prefix('catalog')->group(function (): void {
        Route::apiResource('categories', CategoryController::class)
            ->middleware([
                'index' => 'permission:category.view',
                'show' => 'permission:category.view',
                'store' => 'permission:category.create',
                'update' => 'permission:category.update',
                'destroy' => 'permission:category.delete',
            ]);

        Route::apiResource('attributes', AttributeController::class)
            ->middleware([
                'index' => 'permission:attribute.view',
                'show' => 'permission:attribute.view',
                'store' => 'permission:attribute.create',
                'update' => 'permission:attribute.update',
                'destroy' => 'permission:attribute.delete',
            ]);
    });
});

Route::post('users/login', [UserController::class, 'login'])->name('users.login');