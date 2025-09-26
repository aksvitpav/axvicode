<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Trait\MultilangRichEditorImageGalleryTrait;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    use MultilangRichEditorImageGalleryTrait;

    public static function configure(Schema $schema): Schema
    {
        $defaultLocale = app()->getLocale();

        return $schema
            ->components([
                TextInput::make('title')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn(Get $get, Set $set, ?string $state) => $set(
                            'slug',
                            Str::slug($get('title.' . app()->getLocale()))
                        )
                    )
                    ->translatableTabs()
                    ->modifyFieldsUsing(function (Field $component, string $locale) use ($defaultLocale) {
                        if ($locale === $defaultLocale) {
                            $component->required();
                        }
                        return $component;
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->maxLength(255)
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->translatableTabs()
                    ->modifyFieldsUsing(self::fillRichEditorTranslatableField($defaultLocale))
                    ->columnSpanFull(),
            ]);
    }
}
