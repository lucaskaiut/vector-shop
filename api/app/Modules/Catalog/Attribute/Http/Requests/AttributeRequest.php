<?php

namespace App\Modules\Catalog\Attribute\Http\Requests;

use App\Modules\Catalog\Attribute\Domain\Enums\AttributeType;
use App\Modules\Catalog\Attribute\Domain\Models\Attribute;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @mixin \Illuminate\Http\Request
 * @method bool isMethod(string $method)
 * @method mixed input(?string $key = null, mixed $default = null)
 * @method bool has(string|array $key)
 * @method mixed route(?string $name = null, mixed $default = null)
 */
class AttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isStore = $this->isMethod('post');
        $typeRule = $isStore ? 'required' : 'sometimes';
        $optionsRule = [
            'nullable',
            'array',
            Rule::requiredIf(function () use ($isStore) {
                if (!$isStore) {
                    return false;
                }

                $type = $this->input('type');

                return $type !== null && in_array($type, AttributeType::optionable(), true);
            }),
        ];

        $orderRule = ['sometimes', 'nullable'];
        $orderRule[] = 'integer';
        $orderRule[] = 'min:0';

        return [
            'name' => [$isStore ? 'required' : 'sometimes', 'string', 'max:255'],
            'order' => $orderRule,
            'type' => [$typeRule, 'string', Rule::in(AttributeType::values())],
            'options' => $optionsRule,
            'options.*.id' => ['sometimes', 'integer', 'exists:attribute_options,id'],
            'options.*.value' => ['nullable', 'string', 'max:255'],
            'options.*.order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'options.*.delete' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator): void {
            $currentType = $this->input('type');
            $attribute = $this->attributeModel();

            if ($currentType === null && $attribute !== null) {
                $currentType = $attribute->type;
            }

            if ($currentType === null) {
                return;
            }

            $options = $this->input('options');
            $optionsProvided = $this->has('options');
            $requiresOptions = in_array($currentType, AttributeType::optionable(), true);

            if ($requiresOptions) {
                $shouldValidatePresence = $this->isMethod('post') || $this->has('type') || $optionsProvided;

                if ($shouldValidatePresence && (empty($options) || !is_array($options))) {
                    $validator->errors()->add('options', 'As opções são obrigatórias para tipos de seleção.');
                    return;
                }

                if (is_array($options)) {
                    $this->validateOptionCommands($validator, $options, $attribute);
                }

                return;
            }

            if ($optionsProvided && !empty($options)) {
                $validator->errors()->add('options', 'Opções só são permitidas para tipos de seleção.');
            }
        });
    }

    protected function validateOptionCommands(ValidatorContract $validator, array $options, ?Attribute $attribute): void
    {
        $attributeOptionIds = $attribute?->options()->pluck('id')->all() ?? [];
        $hasCreatableOption = false;

        foreach ($options as $index => $option) {
            $optionId = $option['id'] ?? null;
            $deleteRequested = filter_var($option['delete'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($optionId !== null) {
                if ($attribute === null) {
                    $validator->errors()->add("options.$index.id", 'Não é possível informar id ao criar novas opções.');
                } elseif (!in_array($optionId, $attributeOptionIds, true)) {
                    $validator->errors()->add("options.$index.id", 'Opção informada não pertence a este atributo.');
                }
            }

            if ($deleteRequested) {
                if ($optionId === null) {
                    $validator->errors()->add("options.$index.id", 'O id é obrigatório para remover uma opção.');
                }

                continue;
            }

            $isNewOption = $optionId === null;

            if ($isNewOption) {
                if (!array_key_exists('value', $option) || $option['value'] === null || $option['value'] === '') {
                    $validator->errors()->add("options.$index.value", 'Informe o valor da nova opção.');
                }

                $hasCreatableOption = true;
                continue;
            }

            $hasUpdates = array_key_exists('value', $option) || array_key_exists('order', $option);

            if (!$hasUpdates) {
                $validator->errors()->add("options.$index", 'Informe pelo menos um campo para atualizar a opção.');
            }
        }

        if ($this->isMethod('post') && !$hasCreatableOption) {
            $validator->errors()->add('options', 'Envie ao menos uma nova opção sem id.');
        }
    }

    protected function attributeModel(): ?Attribute
    {
        $attributeId = $this->route('attribute');

        if (!$attributeId) {
            return null;
        }

        return Attribute::find($attributeId);
    }
}


