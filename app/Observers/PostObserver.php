<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Tag;

class PostObserver
{
    public function updated(Post $post): void
    {
        if ($post->isDirty('tags') || $post->wasChanged()) {
            static::cleanupUnusedTags();
        }
    }

    public function deleted(Post $post): void
    {
        static::cleanupUnusedTags();
    }

    protected static function cleanupUnusedTags(): void
    {
        Tag::whereDoesntHave('posts')->delete();
    }
}
