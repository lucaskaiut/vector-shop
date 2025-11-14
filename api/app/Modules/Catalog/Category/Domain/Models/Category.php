<?php

namespace App\Modules\Catalog\Category\Domain\Models;

use App\Modules\Company\Domain\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'description',
        'sort_order',
        'url',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}

