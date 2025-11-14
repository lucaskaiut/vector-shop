<?php

namespace App\Modules\Catalog\Category\Domain;

use App\Modules\Catalog\Category\Domain\Filters\CategoryFilter;
use App\Modules\Catalog\Category\Domain\Models\Category;
use App\Modules\Core\Domain\Serivce;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryService extends Serivce
{
    protected array $with = ['parent', 'company'];

    public function __construct(Category $category, CategoryFilter $filter)
    {
        parent::__construct($category, $filter);
    }

    public function create(array $data): Model
    {
        $data = $this->prepareUrl($data);
        $category = parent::create($data);
        $this->updateChildrenUrls($category);

        return $category;
    }

    public function update(Model $model, array $data): Model
    {
        if (!$model instanceof Category) {
            return parent::update($model, $data);
        }

        $oldParentId = $model->parent_id;

        if (!isset($data['url']) || empty($data['url'])) {
            $parentIdChanged = isset($data['parent_id']) && $data['parent_id'] != $oldParentId;
            $nameChanged = isset($data['name']) && $data['name'] != $model->name;

            if ($parentIdChanged || $nameChanged) {
                $data = $this->prepareUrl($data, $model);
            }
        }

        $category = parent::update($model, $data);
        $category->refresh();
        $this->updateChildrenUrls($category);

        return $category;
    }

    public function delete(Model $model): bool
    {
        if (!$model instanceof Category) {
            return parent::delete($model);
        }

        $children = $model->children()->get();
        $deleted = parent::delete($model);

        if ($deleted) {
            foreach ($children as $child) {
                $this->updateUrlForOrphanedCategory($child);
            }
        }

        return $deleted;
    }

    protected function prepareUrl(array $data, ?Category $category = null): array
    {
        if (isset($data['url']) && !empty($data['url'])) {
            return $data;
        }

        $name = $data['name'] ?? ($category?->name ?? '');
        $baseUrl = Str::slug($name);

        if (isset($data['parent_id']) && $data['parent_id']) {
            $parent = $this->find($data['parent_id']);
            if ($parent instanceof Category && $parent->url) {
                $parentUrl = rtrim($parent->url, '/');
                $data['url'] = $parentUrl . '/' . $baseUrl;
            } else {
                $data['url'] = $baseUrl;
            }
        } elseif ($category && $category->parent_id) {
            $parent = $category->parent;
            if ($parent && $parent->url) {
                $parentUrl = rtrim($parent->url, '/');
                $data['url'] = $parentUrl . '/' . $baseUrl;
            } else {
                $data['url'] = $baseUrl;
            }
        } else {
            $data['url'] = $baseUrl;
        }

        $originalUrl = $data['url'];
        $counter = 1;

        while ($this->urlExists($data['url'], $category?->id)) {
            $data['url'] = $originalUrl . '-' . $counter;
            $counter++;
        }

        return $data;
    }

    protected function urlExists(string $url, ?int $excludeId = null): bool
    {
        $query = Category::where('url', $url);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    protected function updateChildrenUrls(Category $category): void
    {
        $children = $category->children()->get();

        foreach ($children as $child) {
            $childName = Str::slug($child->name);
            $newUrl = rtrim($category->url, '/') . '/' . $childName;

            $originalUrl = $newUrl;
            $counter = 1;

            while ($this->urlExists($newUrl, $child->id)) {
                $newUrl = $originalUrl . '-' . $counter;
                $counter++;
            }

            if ($child->url !== $newUrl) {
                $child->url = $newUrl;
                $child->save();
                $this->updateChildrenUrls($child);
            }
        }
    }

    protected function updateUrlForOrphanedCategory(Category $category): void
    {
        $childName = Str::slug($category->name);
        $newUrl = $childName;

        $originalUrl = $newUrl;
        $counter = 1;

        while ($this->urlExists($newUrl, $category->id)) {
            $newUrl = $originalUrl . '-' . $counter;
            $counter++;
        }

        if ($category->url !== $newUrl) {
            $category->url = $newUrl;
            $category->save();
            $this->updateChildrenUrls($category);
        }
    }
}

