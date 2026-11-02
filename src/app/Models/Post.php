<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // 一括で保存してよいカラム
    protected $fillable = ['title', 'body', 'image_path'];

    // この記事を書いたユーザー(記事は1人のユーザーに属する)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // この記事をお気に入りしたユーザー
    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}