<?php

namespace App\Modules\Catalog\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category');

        $urlRule = Rule::unique('categories', 'url');

        if ($categoryId) {
            $urlRule = $urlRule->ignore($categoryId);
        }

        $parentIdRules = ['nullable', 'integer', 'exists:categories,id'];

        if ($categoryId) {
            $parentIdRules[] = Rule::notIn([$categoryId]);
        }

        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'url' => ['nullable', 'string', 'max:255', $urlRule],
                'parent_id' => $parentIdRules,
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string'],
                'meta_keywords' => ['nullable', 'string', 'max:255'],
            ];
        }

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'url' => ['sometimes', 'nullable', 'string', 'max:255', $urlRule],
            'parent_id' => $parentIdRules,
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'meta_keywords' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->route('category');
            $parentId = $this->input('parent_id');

            if (!$categoryId || !$parentId) {
                return;
            }

            $category = \App\Modules\Catalog\Category\Domain\Models\Category::find($categoryId);

            if (!$category) {
                return;
            }

            $parent = \App\Modules\Catalog\Category\Domain\Models\Category::find($parentId);

            if (!$parent) {
                return;
            }

            if ($this->isDescendant($category, $parent)) {
                $validator->errors()->add('parent_id', 'Uma categoria não pode ser filha de seus próprios descendentes.');
            }
        });
    }

    protected function isDescendant($category, $potentialParent): bool
    {
        $current = $category->children;

        foreach ($current as $child) {
            if ($child->id === $potentialParent->id) {
                return true;
            }

            if ($this->isDescendant($child, $potentialParent)) {
                return true;
            }
        }

        return false;
    }
}

