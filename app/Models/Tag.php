<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "id",
        "name",
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            Item::class,
            "tag_item",
            "id_tag",
            "id_item"
        );
    }
}
