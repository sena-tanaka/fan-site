<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ログインしていないと、どのページも見られない(F-29)
Route::middleware('auth')->group(function () {
    // トップ(タスク6で TopController に置き換える)
    Route::get('/', function () {
        return view('top');
    })->name('home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ブログ(URLは /blog、ルート名は posts.*)
    Route::resource('blog', PostController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->names('posts')
        ->parameters(['blog' => 'post']);

    Route::post('/blog/{post}/favorite', [FavoriteController::class, 'toggle'])->name('posts.favorite');
});

// ログイン・会員登録などの画面(ここはログインしていなくても開ける)
require __DIR__.'/auth.php';