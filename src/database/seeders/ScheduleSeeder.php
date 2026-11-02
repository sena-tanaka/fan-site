<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            // これからの予定(実行した日から数えた日付にする)
            [
                'title'       => 'WINTER DANCE LIVE 2026',
                'starts_at'   => now()->addDays(14)->setTime(18, 0),
                'opens_at'    => now()->addDays(14)->setTime(17, 30),
                'venue'       => '市民ホール 大ホール',
                'address'     => '東京都〇〇区1-2-3',
                'description' => '冬のワンマンライブ。新曲も披露します！',
            ],
            [
                'title'       => 'ストリートダンスフェス',
                'starts_at'   => now()->addDays(30)->setTime(13, 0),
                'opens_at'    => null,
                'venue'       => '中央公園 野外ステージ',
                'address'     => null,
                'description' => '入場無料のイベントに出演します。',
            ],
            // 過去の予定
            [
                'title'       => 'ハロウィンダンスイベント',
                'starts_at'   => now()->subDays(5)->setTime(15, 0),
                'opens_at'    => now()->subDays(5)->setTime(14, 30),
                'venue'       => '駅前ひろば',
                'address'     => null,
                'description' => 'たくさんのご来場ありがとうございました！',
            ],
        ];

        foreach ($schedules as $schedule) {
            // 同じタイトルがあれば更新、なければ作成(何回実行しても重複しない)
            Schedule::updateOrCreate(['title' => $schedule['title']], $schedule);
        }
    }
}