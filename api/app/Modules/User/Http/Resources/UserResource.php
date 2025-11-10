<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $token = Arr::get($this->additional, 'token');

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'token' => $this->when(!is_null($token), $token),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

