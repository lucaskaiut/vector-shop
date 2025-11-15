<?php

namespace App\Modules\Catalog\Attribute\Domain\Models;

use App\Modules\Company\Domain\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'order',
        'type',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('order');
    }
}


