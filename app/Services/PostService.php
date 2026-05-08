<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $image = $attributes['image'] ?? null;
        unset($attributes['image']);

        $storedImagePath = null;

        try {
            return DB::transaction(function () use ($userId, $attributes, $image, &$storedImagePath): Post {
                if ($image instanceof UploadedFile) {
                    $storedImagePath = $image->store('posts', 'public');
                    $attributes['image_url'] = Storage::disk('public')->url($storedImagePath);
                }

                $post = Post::create([
                    ...$attributes,
                    'user_id' => $userId,
                ]);

                return $post->load('user')->loadCount('comments');
            });
        } catch (Throwable $exception) {
            if ($storedImagePath !== null) {
                Storage::disk('public')->delete($storedImagePath);
            }

            throw new ApiException(
                message: 'Failed to create post',
                statusCode: 500,
                previous: $exception
            );
        }
    }
}
