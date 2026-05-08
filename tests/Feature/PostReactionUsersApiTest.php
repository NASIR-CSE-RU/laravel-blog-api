<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PostReactionUsersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_includes_reaction_user_names_for_posts(): void
    {
        $viewer = User::factory()->create();
        $firstReactor = User::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $secondReactor = User::factory()->create([
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
        ]);
        $post = Post::factory()->publicVisibility()->create();

        Reaction::create([
            'user_id' => $firstReactor->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LIKE,
        ]);
        Reaction::create([
            'user_id' => $secondReactor->id,
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'type' => Reaction::TYPE_LOVE,
        ]);

        Passport::actingAs($viewer);

        $response = $this->getJson('/api/feeds');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.reaction_users.0.first_name', 'Grace')
            ->assertJsonPath('data.0.reaction_users.0.last_name', 'Hopper')
            ->assertJsonPath('data.0.reaction_users.1.first_name', 'Ada')
            ->assertJsonPath('data.0.reaction_users.1.last_name', 'Lovelace');
    }
}
