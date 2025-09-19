<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use SoftDeletes;
    use HasTranslations;

    protected $fillable = ['title', 'slug'];

    public array $translatable = ['title'];

    public function posts(): BelongsToMany
    {
        return $this
            ->belongsToMany(Post::class)
            ->withPivot('order')
            ->withTimestamps();
    }
}
