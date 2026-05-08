<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_reaction_uses_morph_alias_and_can_be_resolved(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $reaction = Reaction::create([
            'user_id' => $user->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LIKE,
        ]);

        $this->assertDatabaseHas('reactions', [
            'id' => $reaction->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LIKE,
        ]);
        $this->assertTrue($reaction->reactable->is($post));
        $this->assertTrue($post->reactions()->first()->is($reaction));
    }

    public function test_comment_reaction_uses_morph_alias_and_can_be_resolved(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $reaction = Reaction::create([
            'user_id' => $user->id,
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'type' => Reaction::TYPE_HAHA,
        ]);

        $this->assertDatabaseHas('reactions', [
            'id' => $reaction->id,
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'type' => Reaction::TYPE_HAHA,
        ]);
        $this->assertTrue($reaction->reactable->is($comment));
        $this->assertTrue($comment->reactions()->first()->is($reaction));
    }
}
