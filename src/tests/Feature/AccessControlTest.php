<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private array $pages = ['/', '/members', '/schedules', '/blog', '/contact'];

    // 未ログインでは、どのページもログイン画面に移動する(F-29)
    public function test_guest_is_redirected_to_login_on_every_page(): void
    {
        foreach ($this->pages as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    // ログインしていれば、どのページも表示できる
    public function test_logged_in_user_can_view_every_page(): void
    {
        $user = User::factory()->create();

        foreach ($this->pages as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }
}