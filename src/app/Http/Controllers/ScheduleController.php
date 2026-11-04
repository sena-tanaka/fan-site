<?php

namespace App\Http\Controllers;

use App\Models\Schedule;

class ScheduleController extends Controller
{
    // 出演スケジュール一覧(F-30)
    public function index()
    {
        // これからの予定: 近い順
        $upcoming = Schedule::where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        return view('schedules.index', compact('upcoming'));
    }

    // 出演スケジュール詳細(F-08)
    public function show(Schedule $schedule)
    {
        return view('schedules.show', compact('schedule'));
    }
}