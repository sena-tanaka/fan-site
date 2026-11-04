<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberController extends Controller
{
    // メンバー一覧(F-06)
    public function index()
    {
        $members = Member::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('members.index', compact('members'));
    }
}