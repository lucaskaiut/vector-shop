<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\Core\Http\Controllers\CoreController;
use App\Modules\User\Domain\UserService;
use App\Modules\User\Http\Requests\UserLoginRequest;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends CoreController
{
    public function __construct(
        private readonly UserService $service,
    )
    {
        parent::__construct(
            $service,
            \App\Modules\User\Http\Resources\UserResource::class,
            \App\Modules\User\Http\Resources\UserCollection::class,
            \App\Modules\User\Http\Requests\UserRequest::class
        );
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $user = $this->service->findOneOrFail(['email' => $data['email']]);

            if (!Hash::check($data['password'], $user->password)) {
                throw new \Exception('Invalid credentials');
            }

            $user->load('profile');
            $token = $user->createToken('auth')->plainTextToken;

            return (new UserResource($user))->additional(['token' => $token])
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        });
    }
}

