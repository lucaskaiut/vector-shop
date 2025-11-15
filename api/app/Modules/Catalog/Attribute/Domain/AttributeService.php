<?php

namespace App\Modules\Catalog\Attribute\Domain;

use App\Modules\Catalog\Attribute\Domain\Enums\AttributeType;
use App\Modules\Catalog\Attribute\Domain\Filters\AttributeFilter;
use App\Modules\Catalog\Attribute\Domain\Models\Attribute;
use App\Modules\Core\Domain\Serivce;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AttributeService extends Serivce
{
    protected array $with = ['options'];

    public function __construct(Attribute $attribute, AttributeFilter $filter)
    {
        parent::__construct($attribute, $filter);
    }

    public function create(array $data): Model
    {
        [$data, $options] = $this->separateOptions($data);
        /** @var Attribute $attribute */
        $attribute = parent::create($data);
        $this->handleOptions($attribute, $options);

        return $attribute->refresh();
    }

    public function update(Model $model, array $data): Model
    {
        if (!$model instanceof Attribute) {
            return parent::update($model, $data);
        }

        [$data, $options] = $this->separateOptions($data);
        $attribute = parent::update($model, $data);
        $this->handleOptions($attribute, $options);

        return $attribute->refresh();
    }

    protected function separateOptions(array $data): array
    {
        if (!array_key_exists('options', $data)) {
            return [$data, null];
        }

        $options = $data['options'];
        unset($data['options']);

        return [$data, $options];
    }

    protected function handleOptions(Attribute $attribute, ?array $options): void
    {
        if (!in_array($attribute->type, AttributeType::optionable(), true)) {
            $attribute->options()->delete();
            $attribute->unsetRelation('options');
            return;
        }

        if ($options === null) {
            return;
        }

        $this->syncRelationCommands(
            $attribute,
            'options',
            $options,
            ['value', 'order'],
            function (array $payload, bool $isUpdate): array {
                return $this->prepareOptionPayload($payload, $isUpdate);
            }
        );
    }

    protected function prepareOptionPayload(array $option, bool $isUpdate = false): array
    {
        if ($isUpdate) {
            $data = [];

            if (array_key_exists('value', $option)) {
                $data['value'] = Arr::get($option, 'value');
            }

            if (array_key_exists('order', $option)) {
                $data['order'] = (int) Arr::get($option, 'order', 0);
            }

            return $data;
        }

        return [
            'value' => Arr::get($option, 'value'),
            'order' => (int) Arr::get($option, 'order', 0),
        ];
    }
}


