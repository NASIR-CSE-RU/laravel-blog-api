<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostService
{
    /**
     * Create a new post for the given user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(int $userId, array $attributes): Post
    {
        try {
            return DB::transaction(function () use ($userId, $attributes): Post {
                $post = Post::create([
                    ...$attributes,
                    'user_id' => $userId,
                ]);

                return $post->load('user');
            });
        } catch (Throwable $exception) {
            throw new ApiException(
                message: 'Failed to create post',
                statusCode: 500,
                previous: $exception
            );
        }
    }
}
