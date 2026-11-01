<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;   // 追加
use App\Models\Post;
use Illuminate\Support\Facades\Storage;    // 追加

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

        // 編集フォームの表示
    public function edit(Post $post)
    {
        $this->authorize('update', $post); // 自分の記事でなければ403

        return view('posts.edit', compact('post'));
    }

    // 更新(画像の差し替え込み)
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $data = $request->safe()->only(['title', 'body']);

        if ($request->hasFile('image')) {
            // 古い画像を削除してから新しい画像を保存
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('posts.show', $post)
            ->with('success', '記事を更新しました！');
    }

        // 削除(画像も一緒に削除)
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post); // 自分の記事でなければ403

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', '記事を削除しました');
    }
}