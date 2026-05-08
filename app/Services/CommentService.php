<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class CommentService
{
    /**
     * @return Collection<int, Comment>
     */
    public function getForPost(int $postId, int $userId): Collection
    {
        try {
            $post = $this->findAccessiblePost($postId, $userId);

            return $post->comments()
                ->whereNull('parent_id')
                ->with('user')
                ->withCount(['replies', 'reactions'])
                ->withExists([
                    'reactions as viewer_has_liked' => fn ($query) => $query
                        ->where('user_id', $userId)
                        ->where('type', Reaction::TYPE_LIKE),
                ])
                ->with([
                    'replies' => fn ($query) => $query
                        ->with('user')
                        ->withCount(['replies', 'reactions'])
                        ->withExists([
                            'reactions as viewer_has_liked' => fn ($reactionQuery) => $reactionQuery
                                ->where('user_id', $userId)
                                ->where('type', Reaction::TYPE_LIKE),
                        ]),
                ])
                ->orderBy('created_at')
                ->get();
        } catch (ApiException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ApiException(
                message: 'Failed to fetch comments',
                statusCode: 500,
                previous: $exception
            );
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(int $postId, int $userId, array $attributes): Comment
    {
        try {
            $this->findAccessiblePost($postId, $userId);
            $parentId = $attributes['parent_id'] ?? null;
            unset($attributes['parent_id']);

            if ($parentId !== null) {
                $parentComment = Comment::query()
                    ->whereKey($parentId)
                    ->where('post_id', $postId)
                    ->first();

                if ($parentComment === null) {
                    throw new ApiException('Parent comment not found', 404);
                }
            }

            $comment = Comment::create([
                ...$attributes,
                'post_id' => $postId,
                'user_id' => $userId,
                'parent_id' => $parentId,
            ]);

            return $comment->load('user')
                ->loadCount(['replies', 'reactions'])
                ->loadExists([
                    'reactions as viewer_has_liked' => fn ($query) => $query
                        ->where('user_id', $userId)
                        ->where('type', Reaction::TYPE_LIKE),
                ]);
        } catch (ApiException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ApiException(
                message: 'Failed to create comment',
                statusCode: 500,
                previous: $exception
            );
        }
    }

    private function findAccessiblePost(int $postId, int $userId): Post
    {
        $post = Post::query()
            ->whereKey($postId)
            ->where(function ($query) use ($userId): void {
                $query->where('visibility', 'public')
                    ->orWhere('user_id', $userId);
            })
            ->first();

        if ($post === null) {
            throw new ApiException('Post not found', 404);
        }

        return $post;
    }
}
