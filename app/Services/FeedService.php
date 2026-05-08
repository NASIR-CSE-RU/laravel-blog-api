<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Post;
use App\Models\Reaction;
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
                    'reactions',
                ])
                ->withExists([
                    'reactions as viewer_has_liked' => fn ($query) => $query
                        ->where('user_id', $userId)
                        ->where('type', Reaction::TYPE_LIKE),
                ])
                ->with([
                    'reactions' => fn ($query) => $query
                        ->with(['user:id,first_name,last_name'])
                        ->orderByDesc('created_at'),
                    'topLevelComments' => fn ($query) => $query
                        ->with('user')
                        ->withCount(['replies', 'reactions'])
                        ->withExists([
                            'reactions as viewer_has_liked' => fn ($reactionQuery) => $reactionQuery
                                ->where('user_id', $userId)
                                ->where('type', Reaction::TYPE_LIKE),
                        ])
                        ->with([
                            'replies' => fn ($replyQuery) => $replyQuery
                                ->with('user')
                                ->withCount('reactions')
                                ->withExists([
                                    'reactions as viewer_has_liked' => fn ($reactionQuery) => $reactionQuery
                                        ->where('user_id', $userId)
                                        ->where('type', Reaction::TYPE_LIKE),
                                ])
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
