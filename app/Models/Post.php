<?php

namespace App\Models;

use Honeystone\Seo\MetadataDirector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Post extends Model implements HasMedia
{
    use SoftDeletes;
    use HasTranslations;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status_id',
        'category_id',
        'tag_id',
        'author_id',
    ];

    public array $translatable = ['title', 'excerpt', 'content'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10)
            ->nonQueued();
    }

    public function seo(): MetadataDirector
    {
        $coverUrl = $this->getFirstMediaUrl('cover') ?: null;

        return seo()
            ->title($this->meta_title ?: $this->title)
            ->description($this->meta_description ?: $this->excerpt)
            ->keywords(...explode(',', $this->meta_keywords ?? null))
            ->url(route('posts.show', $this->slug))
            ->images($coverUrl)
            ->twitterImage($coverUrl)
            ->jsonLdImages([$coverUrl])
            ->canonical(route('posts.show', $this->slug));
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PostStatus::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('pivot_order');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
