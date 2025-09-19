<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
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

    private static function fillRichEditorTranslatableField(string $defaultLocale): \Closure
    {
        return function (Field $component, string $locale) use ($defaultLocale) {
            if ($locale === $defaultLocale) {
                $component->required();
            }

            $component->saveRelationshipsUsing(
                function (RichEditor $component, ?array $rawState, $record) use ($locale) {
                    $fileAttachmentProvider = $component->getFileAttachmentProvider();

                    if (! $fileAttachmentProvider) {
                        return;
                    }

                    if (! $fileAttachmentProvider->isExistingRecordRequiredToSaveNewFileAttachments()) {
                        return;
                    }

                    if (! $record->wasRecentlyCreated) {
                        return;
                    }

                    $fileAttachmentIds = [];

                    $component->rawState(
                        $component->getTipTapEditor()
                            ->setContent(
                                $rawState ?? [
                                'type' => 'doc',
                                'content' => [],
                            ]
                            )
                            ->descendants(
                                function (object &$node) use ($component, &$fileAttachmentIds): void {
                                    if ($node->type !== 'image') {
                                        return;
                                    }

                                    if (blank($node->attrs->id ?? null)) {
                                        return;
                                    }

                                    $attachment = $component->getUploadedFileAttachment($node->attrs->id);

                                    if ($attachment) {
                                        $node->attrs->id = $component->saveUploadedFileAttachment(
                                            $attachment
                                        );
                                        $node->attrs->src = $component->getFileAttachmentUrl(
                                            $node->attrs->id
                                        );

                                        $fileAttachmentIds[] = $node->attrs->id;

                                        return;
                                    }

                                    if (filled($component->getFileAttachmentUrl($node->attrs->id))) {
                                        $fileAttachmentIds[] = $node->attrs->id;

                                        return;
                                    }

                                    $fileAttachmentIdFromAnotherRecord = $component->saveFileAttachmentFromAnotherRecord(
                                        $node->attrs->id
                                    );

                                    if (blank($fileAttachmentIdFromAnotherRecord)) {
                                        $fileAttachmentIds[] = $node->attrs->id;

                                        return;
                                    }

                                    $node->attrs->id = $fileAttachmentIdFromAnotherRecord;
                                    $node->attrs->src = $component->getFileAttachmentUrl(
                                        $fileAttachmentIdFromAnotherRecord
                                    ) ?? $node->attrs->src ?? null;
                                }
                            )
                            ->getDocument(),
                    );

                    $attributeWithoutLocale = Str::before(
                        $component->getContentAttribute()->getName(),
                        '.'
                    );

                    if ($record->isTranslatableAttribute($attributeWithoutLocale)) {
                        $record->setTranslation($attributeWithoutLocale, $locale, $component->getState());
                    } else {
                        $record->setAttribute(
                            $component->getContentAttribute()->getName(),
                            $component->getState()
                        );
                    }

                    $record->save();

                    $fileAttachmentProvider->cleanUpFileAttachments(exceptIds: $fileAttachmentIds);
                }
            );

            return $component;
        };
    }
}
