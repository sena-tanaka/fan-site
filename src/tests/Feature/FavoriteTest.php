<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // いいねを押すと登録され、もう一度押すと取り消される(JSONで返る)
    public function test_favorite_can_be_toggled(): void
    {
        $dancer = User::factory()->create(['role' => UserRole::Dancer]);
        $post   = $dancer->posts()->create(['title' => 'テスト記事', 'body' => '本文']);
        $fan    = User::factory()->create();

        $this->actingAs($fan)
            ->postJson(route('posts.favorite', $post))
            ->assertOk()
            ->assertJson(['favorited' => true, 'count' => 1]);

        $this->actingAs($fan)
            ->postJson(route('posts.favorite', $post))
            ->assertOk()
            ->assertJson(['favorited' => false, 'count' => 0]);
    }
}