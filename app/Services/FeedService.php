<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class FeedService
{
    /**
     * Get the feed posts for the authenticated user.
     *
     * @return Collection<int, Post>
     */
    public function getForUser(int $userId): Collection
    {
        try {
            return Post::query()
                ->with('user')
                ->where(function ($query) use ($userId): void {
                    $query->where('visibility', 'public')
                        ->orWhere('user_id', $userId);
                })
                ->orderByDesc('created_at')
                ->get();
        } catch (Throwable $exception) {
            throw new ApiException(
                message: 'Failed to fetch feed',
                statusCode: 500,
                previous: $exception
            );
        }
    }
}
