<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            foreach ($this->getUsers() as $user) {
                $currentUser = User::query()->updateOrCreate(
                    ['email' => $user['data']['email']],
                    $user['data']
                );
            }
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            $this->command->error($exception->getMessage());
        }
    }

    private function getUsers(): array
    {
        return [
            [
                'data' => [
                    'name' => 'Admin',
                    'email' => 'axvicode@gmail.com',
                    'password' => Hash::make(config('auth.admin_password')),
                ],
            ],
        ];
    }
}
