<?php

namespace App\Modules\Acl\Http\Requests;

use App\Modules\Acl\Domain\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionRule = Rule::in(Permission::values());

        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'company_id' => ['sometimes', 'integer', 'exists:companies,id'],
                'permissions' => ['required', 'array'],
                'permissions.*' => ['string', $permissionRule],
            ];
        }

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'company_id' => ['sometimes', 'integer', 'exists:companies,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', $permissionRule],
        ];
    }
}
