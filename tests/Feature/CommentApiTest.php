<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_comments_for_accessible_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();
        $comment = Comment::factory()->for($post)->create();
        Comment::factory()->for($post)->create([
            'parent_id' => $comment->id,
        ]);

        Passport::actingAs($user);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Comments fetched successfully')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $comment->id)
            ->assertJsonPath('data.0.replies_count', 1)
            ->assertJsonPath('data.0.replies.0.parent_id', $comment->id);
    }

    public function test_authenticated_user_can_create_comment_for_public_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();

        Passport::actingAs($user);

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'This is a test comment.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Comment created successfully')
            ->assertJsonPath('data.post_id', $post->id)
            ->assertJsonPath('data.content', 'This is a test comment.')
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'This is a test comment.',
        ]);
    }

    public function test_authenticated_user_can_create_reply_in_same_comments_table(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();
        $parentComment = Comment::factory()->for($post)->create();

        Passport::actingAs($user);

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'This is a reply.',
            'parent_id' => $parentComment->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.parent_id', $parentComment->id)
            ->assertJsonPath('data.post_id', $post->id)
            ->assertJsonPath('data.content', 'This is a reply.');

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'content' => 'This is a reply.',
        ]);
    }

    public function test_user_cannot_access_comments_for_another_users_private_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->privateVisibility()->create();

        Passport::actingAs($user);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Post not found');
    }
}
