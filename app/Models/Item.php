<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        "id",
        "name",
        "id_category",
        "id_tags",
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, "id_category");
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            "tag_item",
            "id_item",
            "id_tag"
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            "tags" => $this->tags,
            "category" => $this->category
        ]);
    }
}
