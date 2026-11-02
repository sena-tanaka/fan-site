<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;   
use App\Models\Post;
use Illuminate\Support\Facades\Storage;    
use Illuminate\Http\Request;   

class PostController extends Controller
{
        // 一覧(検索・並び替え・ページネーション付き)
    public function index(Request $request)
    {
        $query = Post::with('user.member');   // 設計書どおり、メンバーまでまとめて読み込む

        // キーワード検索(タイトルか本文に含まれる記事)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // 並び替え
        if ($request->get('sort') === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $posts = $query->paginate(6)->withQueryString();

        return view('posts.index', compact('posts'));
    }

       // 投稿フォームの表示
    public function create()
    {
        $this->authorize('create', Post::class); // ダンサー以外は403

        return view('posts.create');
    }

    // 保存(画像処理込み)
    public function store(StorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        $data = $request->safe()->only(['title', 'body']);
        // ...(以下はそのまま)
        
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
        $post->load('user.member'); // 詳細画面でメンバー名も使うので一緒に読み込む

        $isFavorited    = auth()->user()->favorites()->where('post_id', $post->id)->exists();
        $favoritesCount = $post->favorites()->count();

        return view('posts.show', compact('post', 'isFavorited', 'favoritesCount'));
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