<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class FeedService
{
    private const COMMENT_PREVIEW_LIMIT = 3;

    private const REPLY_PREVIEW_LIMIT = 3;

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
                ->withCount([
                    'topLevelComments as comments_count',
                ])
                ->with([
                    'topLevelComments' => fn ($query) => $query
                        ->with('user')
                        ->withCount('replies')
                        ->with([
                            'replies' => fn ($replyQuery) => $replyQuery
                                ->with('user')
                                ->limit(self::REPLY_PREVIEW_LIMIT),
                        ])
                        ->orderByDesc('created_at')
                        ->limit(self::COMMENT_PREVIEW_LIMIT),
                ])
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
