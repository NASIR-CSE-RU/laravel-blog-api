<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ReactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_react_to_public_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LOVE,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Reaction created successfully')
            ->assertJsonPath('data.reactable_type', 'post')
            ->assertJsonPath('data.reactable_id', $post->id)
            ->assertJsonPath('data.type', Reaction::TYPE_LOVE)
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LOVE,
        ]);
    }

    public function test_reacting_again_to_same_post_updates_existing_reaction(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();
        Reaction::create([
            'user_id' => $user->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LIKE,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LOVE,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Reaction removed successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseCount('reactions', 0);
    }

    public function test_second_toggle_recreates_reaction_after_removal(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->publicVisibility()->create();

        Passport::actingAs($user);

        $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_DISLIKE,
        ])->assertCreated();

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_HAHA,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Reaction removed successfully');

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_HAHA,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Reaction created successfully')
            ->assertJsonPath('data.type', Reaction::TYPE_HAHA);

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $user->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_DISLIKE,
        ]);
        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_HAHA,
        ]);
    }

    public function test_authenticated_user_can_react_to_accessible_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->for(Post::factory()->publicVisibility())->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'type' => Reaction::TYPE_LIKE,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Reaction created successfully')
            ->assertJsonPath('data.reactable_type', 'comment')
            ->assertJsonPath('data.reactable_id', $comment->id)
            ->assertJsonPath('data.type', Reaction::TYPE_LIKE);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'type' => Reaction::TYPE_LIKE,
        ]);
    }

    public function test_user_cannot_react_to_comment_on_another_users_private_post(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->for(Post::factory()->privateVisibility())->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'type' => Reaction::TYPE_LIKE,
        ]);

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Comment not found');
    }

    public function test_toggle_requires_valid_reactable_type(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->postJson('/api/reactions/toggle', [
            'reactable_type' => 'article',
            'reactable_id' => 1,
            'type' => Reaction::TYPE_LIKE,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed');
    }
}
