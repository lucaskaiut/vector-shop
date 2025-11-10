<?php

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @mixin \Illuminate\Http\Request
 * @method mixed route(string|null $name = null, mixed $default = null)
 * @method bool isMethod(string $method)
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        $emailRule = Rule::unique('users', 'email');

        if ($userId) {
            $emailRule = $emailRule->ignore($userId);
        }

        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', $emailRule],
                'password' => ['required', 'string', 'min:6'],
                'company_id' => ['required', 'integer', 'exists:companies,id'],
            ];
        }

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', $emailRule],
            'password' => ['sometimes', 'string', 'min:6'],
            'company_id' => ['sometimes', 'integer', 'exists:companies,id'],
        ];
    }
}
