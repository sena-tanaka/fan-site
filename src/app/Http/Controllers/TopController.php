<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Post;
use App\Models\Schedule;

class TopController extends Controller
{
    public function __invoke()
    {
        // NEXT STAGE: これからの予定のうち、いちばん近い1件
        $nextStage = Schedule::where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->first();

        // チーム紹介: 活動中のメンバーを並び順で
        $members = Member::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // 最新のブログ(トップでスライド表示する)
        $latestPosts = Post::with('user.member')
            ->latest()
            ->take(8)
            ->get();

        return view('top', compact('nextStage', 'members', 'latestPosts'));
    }
}
