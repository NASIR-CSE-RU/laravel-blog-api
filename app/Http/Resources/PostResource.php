<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin \App\Models\Post */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'visibility' => $this->visibility,
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'reactions_count' => $this->whenCounted('reactions'),
            'viewer_has_liked' => $this->when(isset($this->viewer_has_liked), (bool) $this->viewer_has_liked),
            'reaction_users' => $this->whenLoaded('reactions', function (): array {
                /** @var Collection<int, \App\Models\Reaction> $reactions */
                $reactions = $this->reactions;

                return $reactions
                    ->map(fn ($reaction): ?array => $reaction->user ? [
                        'id' => $reaction->user->id,
                        'first_name' => $reaction->user->first_name,
                        'last_name' => $reaction->user->last_name,
                    ] : null)
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->all();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('topLevelComments')),
        ];
    }
}
