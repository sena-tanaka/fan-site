<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;

class PostController extends Controller
{
    // 一覧(ページネーション付き)
    public function index()
    {
        $posts = Post::with('user.member')   // 設計書どおり、メンバーまでまとめて読み込む
            ->latest()
            ->paginate(6);

        return view('posts.index', compact('posts'));
    }

    // 投稿フォームの表示
    public function create()
    {
        return view('posts.create');
    }

    // 保存(画像処理込み)
    public function store(StorePostRequest $request)
    {
         $data = $request->safe()->only(['title', 'body']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        $post = auth()->user()->posts()->create($data);

        return redirect()->route('posts.show', $post)
             ->with('success', '記事を投稿しました！');
    }

    // 詳細
    public function show(Post $post)
    {
        $post->load('user');

        return view('posts.show', compact('post'));
    }
}