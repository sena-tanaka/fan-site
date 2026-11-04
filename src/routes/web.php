<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ScheduleController;

// ログインしていないと、どのページも見られない(F-29)
Route::middleware('auth')->group(function () {
        // トップ
    Route::get('/', TopController::class)->name('home');

    // メンバー一覧(F-06)
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 出演スケジュール(一覧・詳細)
    Route::resource('schedules', ScheduleController::class)->only(['index', 'show']);

    // ブログ(URLは /blog、ルート名は posts.*)
    Route::resource('blog', PostController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->names('posts')
        ->parameters(['blog' => 'post']);

    Route::post('/blog/{post}/favorite', [FavoriteController::class, 'toggle'])->name('posts.favorite');
});

// ログイン・会員登録などの画面(ここはログインしていなくても開ける)
require __DIR__.'/auth.php';