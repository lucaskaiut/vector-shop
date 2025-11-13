<?php

namespace App\Modules\Acl\Domain;

use App\Modules\Acl\Domain\Models\Profile;
use App\Modules\Core\Domain\Serivce;
use Illuminate\Database\Eloquent\Model;

class ProfileService extends Serivce
{
    public function __construct(Profile $profile)
    {
        parent::__construct($profile);
    }

    public function save(Model $model, array $data): Model
    {
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $model->permissions = $data['permissions'];
            unset($data['permissions']);
        }

        $model->fill($data);
        $model->save();

        return $model->refresh();
    }
}
