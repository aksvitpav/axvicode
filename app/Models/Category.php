<?php

namespace App\Models;

use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasRichContent, HasMedia
{
    use SoftDeletes;
    use HasTranslations;
    use InteractsWithMedia;
    use InteractsWithRichContent;

    /**
     * @param bool $deletePreservingMedia
     */
    protected $fillable = ['title', 'slug', 'description'];

    public array $translatable = ['title', 'description'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function setUpRichContent(): void
    {
        foreach (config('app.available_locales') as $locale) {
            $this->registerRichContent("description.{$locale}")
                ->fileAttachmentProvider(
                    SpatieMediaLibraryFileAttachmentProvider::make()
                        ->collection("category-description-{$locale}"),
                );
        }
    }
}
