<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "id",
        "name",
        "id_category",
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function childrenCategories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
