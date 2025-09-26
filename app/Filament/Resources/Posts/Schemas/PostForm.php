<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\PostStatus;
use App\Models\Tag;
use App\Trait\MultilangRichEditorImageGalleryTrait;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    use MultilangRichEditorImageGalleryTrait;

    public static function configure(Schema $schema): Schema
    {
        $defaultLocale = app()->getLocale();

        return $schema
            ->columns(3)
            ->components([
                Section::make(__('Main content'))
                    ->columnSpan(2)
                    ->schema([
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

                        Textarea::make('excerpt')
                            ->rows(3)
                            ->translatableTabs()
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->translatableTabs()
                            ->modifyFieldsUsing(self::fillRichEditorTranslatableField($defaultLocale))
                            ->columnSpanFull(),

                        FileUpload::make('cover')
                            ->image()
                            ->disk('public')
                            ->directory('posts')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Settings'))
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status_id')
                            ->relationship('status', 'title')
                            ->required()
                            ->default(function () {
                                return PostStatus::first()?->id;
                            }),

                        Select::make('category_id')
                            ->relationship('category', 'title')
                            ->searchable()
                            ->preload(),

                        Select::make('tags')
                            ->relationship('tags', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionUsing(function (array $data) {
                                return Tag::create([
                                    'title' => $data['title'],
                                    'slug' => Str::slug($data['title']),
                                ])->getKey();
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return Tag::where('title', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('title', 'id');
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => Tag::find($value)?->title),


                        Select::make('author_id')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),

                        TextInput::make('meta_title')
                            ->maxLength(255)
                            ->translatableTabs(),

                        Textarea::make('meta_description')
                            ->rows(3)
                            ->maxLength(160)
                            ->translatableTabs(),

                        TextInput::make('meta_keywords')
                            ->maxLength(255)
                            ->translatableTabs(),
                    ]),
            ]);
    }
}
