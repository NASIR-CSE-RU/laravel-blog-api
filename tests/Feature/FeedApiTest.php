<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FeedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_is_paginated(): void
    {
        $viewer = User::factory()->create();
        Post::factory()->count(12)->publicVisibility()->create();

        Passport::actingAs($viewer);

        $response = $this->getJson('/api/feeds?per_page=5&page=2');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Feed fetched successfully')
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.pagination.total', 12)
            ->assertJsonPath('meta.pagination.count', 5)
            ->assertJsonPath('meta.pagination.per_page', 5)
            ->assertJsonPath('meta.pagination.current_page', 2)
            ->assertJsonPath('meta.pagination.total_pages', 3)
            ->assertJsonPath('meta.pagination.has_more_pages', true);
    }

    public function test_feed_includes_reaction_counts_and_like_state(): void
    {
        $viewer = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();
        $comment = Comment::factory()->for($post)->create();
        $reply = Comment::factory()->for($post)->create([
            'parent_id' => $comment->id,
        ]);

        Reaction::create([
            'user_id' => $viewer->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LIKE,
        ]);
        Reaction::create([
            'user_id' => User::factory()->create()->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LOVE,
        ]);
        Reaction::create([
            'user_id' => $viewer->id,
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'type' => Reaction::TYPE_LIKE,
        ]);
        Reaction::create([
            'user_id' => $viewer->id,
            'reactable_type' => 'comment',
            'reactable_id' => $reply->id,
            'type' => Reaction::TYPE_LIKE,
        ]);

        Passport::actingAs($viewer);

        $response = $this->getJson('/api/feeds');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Feed fetched successfully')
            ->assertJsonPath('data.0.id', $post->id)
            ->assertJsonPath('data.0.reactions_count', 2)
            ->assertJsonPath('data.0.viewer_has_liked', true)
            ->assertJsonPath('data.0.comments.0.id', $comment->id)
            ->assertJsonPath('data.0.comments.0.reactions_count', 1)
            ->assertJsonPath('data.0.comments.0.viewer_has_liked', true)
            ->assertJsonPath('data.0.comments.0.replies.0.id', $reply->id)
            ->assertJsonPath('data.0.comments.0.replies.0.reactions_count', 1)
            ->assertJsonPath('data.0.comments.0.replies.0.viewer_has_liked', true);
    }
}
