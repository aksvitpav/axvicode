<?php

namespace Database\Seeders;

use App\Models\PostStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostStatusSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            foreach ($this->getStatuses() as $slug => $titleTranslations) {
                PostStatus::query()->updateOrCreate(
                    ['slug' => $slug],
                    ['title' => $titleTranslations]
                );
            }
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            $this->command->error($exception->getMessage());
        }
    }

    private function getStatuses(): array
    {
        return [
            PostStatus::DRAFT_SLUG => [
                'en' => 'Draft',
                'uk' => 'Чернетка',
            ],
            PostStatus::PUBLISHED_SLUG => [
                'en' => 'Published',
                'uk' => 'Опубліковано'
            ],
            PostStatus::ARCHIVED_SLUG => [
                'en' => 'Archived',
                'uk' => 'Архів'
            ],
        ];
    }
}
