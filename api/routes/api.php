<?php

use App\Modules\Acl\Http\Controllers\ProfileController;
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
            'index' => 'permission:user.view',
            'show' => 'permission:user.view',
            'store' => 'permission:user.update',
            'update' => 'permission:user.update',
            'destroy' => 'permission:user.update',
        ]);
});

Route::post('users/login', [UserController::class, 'login'])->name('users.login');