<?php

namespace App\Http\Controllers;

use App\Models\Post;

class FavoriteController extends Controller
{
    // お気に入りの登録・解除を切り替えて、結果をJSONで返す
    public function toggle(Post $post)
    {
        $user = auth()->user();

        if ($user->favorites()->where('post_id', $post->id)->exists()) {
            $user->favorites()->detach($post->id);
            $favorited = false;
        } else {
            $user->favorites()->attach($post->id);
            $favorited = true;
        }

        return response()->json([
            'favorited' => $favorited,
            'count'     => $post->favorites()->count(),
        ]);
    }
}