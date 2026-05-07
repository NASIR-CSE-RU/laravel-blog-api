<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $users = User::factory(10)->create();
        }

        $users->each(function (User $user): void {
            Post::factory(rand(2, 6))->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
