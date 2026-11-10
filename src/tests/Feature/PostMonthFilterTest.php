<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMonthFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(User $user, string $title, string $createdAt): void
    {
        Post::forceCreate([
            'user_id' => $user->id,
            'title' => $title,
            'body' => '本文',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    // 月を選ぶと、その月に投稿された記事だけが表示される
    public function test_posts_can_be_filtered_by_month(): void
    {
        $user = User::factory()->create();
        $this->makePost($user, '十月の記事', '2026-10-31 23:59:00');
        $this->makePost($user, '十一月の記事', '2026-11-01 00:00:00');
        $this->makePost($user, '十二月の記事', '2026-12-01 00:00:00');

        $this->actingAs($user)->get('/blog?month=2026-11')
            ->assertOk()
            ->assertSee('十一月の記事')
            ->assertDontSee('十月の記事')
            ->assertDontSee('十二月の記事');
    }

    // プルダウンには記事がある月だけが並ぶ
    public function test_month_options_list_only_months_with_posts(): void
    {
        $user = User::factory()->create();
        $this->makePost($user, 'A', '2026-09-10 12:00:00');
        $this->makePost($user, 'B', '2026-11-05 12:00:00');

        $this->actingAs($user)->get('/blog')
            ->assertOk()
            ->assertSee('2026年11月')
            ->assertSee('2026年9月')
            ->assertDontSee('2026年10月');
    }

    // おかしな値が来ても絞り込まずに表示できる
    public function test_invalid_month_is_ignored(): void
    {
        $user = User::factory()->create();
        $this->makePost($user, '記事', '2026-11-05 12:00:00');

        $this->actingAs($user)->get('/blog?month=2026-13')->assertOk()->assertSee('記事');
        $this->actingAs($user)->get('/blog?month=abc')->assertOk()->assertSee('記事');
    }
}
