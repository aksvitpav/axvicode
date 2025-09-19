<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PostStatus extends Model
{
    use SoftDeletes;
    use HasTranslations;

    const string DRAFT_SLUG = 'draft';
    const string PUBLISHED_SLUG = 'published';
    const string ARCHIVED_SLUG = 'archived';

    protected $fillable = ['title', 'slug'];

    public array $translatable = ['title'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
