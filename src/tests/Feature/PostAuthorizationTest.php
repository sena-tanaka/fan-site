<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function dancer(): User
    {
        return User::factory()->create(['role' => UserRole::Dancer]);
    }

    private function fan(): User
    {
        return User::factory()->create(['role' => UserRole::Fan]);
    }

    private function postOf(User $user): Post
    {
        return $user->posts()->create(['title' => 'テスト記事', 'body' => '本文']);
    }

    // ファンは投稿フォームを開けない
    public function test_fan_cannot_open_create_form(): void
    {
        $this->actingAs($this->fan())
            ->get(route('posts.create'))
            ->assertForbidden();
    }

    // ファンは投稿できない
    public function test_fan_cannot_store_post(): void
    {
        $this->actingAs($this->fan())
            ->post(route('posts.store'), ['title' => 'ファンの記事', 'body' => '本文'])
            ->assertForbidden();

        $this->assertDatabaseCount('posts', 0);
    }

    // ダンサーは投稿できる
    public function test_dancer_can_store_post(): void
    {
        $dancer = $this->dancer();

        $this->actingAs($dancer)
            ->post(route('posts.store'), ['title' => 'ダンサーの記事', 'body' => '本文'])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', ['title' => 'ダンサーの記事', 'user_id' => $dancer->id]);
    }

    // 他人の記事の編集画面は403
    public function test_other_user_cannot_edit_post(): void
    {
        $post = $this->postOf($this->dancer());

        $this->actingAs($this->dancer())
            ->get(route('posts.edit', $post))
            ->assertForbidden();
    }

    // 他人の記事は更新できない
    public function test_other_user_cannot_update_post(): void
    {
        $post = $this->postOf($this->dancer());

        $this->actingAs($this->dancer())
            ->put(route('posts.update', $post), ['title' => '書き換え', 'body' => '本文'])
            ->assertForbidden();

        $this->assertDatabaseMissing('posts', ['title' => '書き換え']);
    }

    // 他人の記事は削除できない
    public function test_other_user_cannot_delete_post(): void
    {
        $post = $this->postOf($this->dancer());

        $this->actingAs($this->dancer())
            ->delete(route('posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    // 自分の記事は削除できる
    public function test_owner_can_delete_post(): void
    {
        $owner = $this->dancer();
        $post  = $this->postOf($owner);

        $this->actingAs($owner)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('posts.index'));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}