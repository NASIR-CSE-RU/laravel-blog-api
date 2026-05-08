<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReactionService
{
    /**
     * @return array{action: 'created'|'removed', reaction: ?Reaction}
     */
    public function toggle(int $userId, string $reactableType, int $reactableId, int $type): array
    {
        try {
            $reactable = $this->findAccessibleReactable($reactableType, $reactableId, $userId);

            return DB::transaction(function () use ($reactable, $userId, $type): array {
                /** @var Reaction|null $existingReaction */
                $existingReaction = $reactable->reactions()
                    ->where('user_id', $userId)
                    ->first();

                if ($existingReaction !== null) {
                    $existingReaction->delete();

                    return [
                        'action' => 'removed',
                        'reaction' => null,
                    ];
                }

                /** @var Reaction $reaction */
                $reaction = $reactable->reactions()->create([
                    'user_id' => $userId,
                    'type' => $type,
                ]);

                return [
                    'action' => 'created',
                    'reaction' => $reaction->load('user'),
                ];
            });
        } catch (ApiException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new ApiException(
                message: 'Failed to toggle reaction',
                statusCode: 500,
                previous: $exception
            );
        }
    }

    private function findAccessibleReactable(string $reactableType, int $reactableId, int $userId): Model
    {
        return match ($reactableType) {
            'post' => $this->findAccessiblePost($reactableId, $userId),
            'comment' => $this->findAccessibleComment($reactableId, $userId),
            default => throw new ApiException('Invalid reactable type', 422),
        };
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

    private function findAccessibleComment(int $commentId, int $userId): Comment
    {
        $comment = Comment::query()
            ->whereKey($commentId)
            ->whereHas('post', function ($query) use ($userId): void {
                $query->where('visibility', 'public')
                    ->orWhere('user_id', $userId);
            })
            ->first();

        if ($comment === null) {
            throw new ApiException('Comment not found', 404);
        }

        return $comment;
    }
}
