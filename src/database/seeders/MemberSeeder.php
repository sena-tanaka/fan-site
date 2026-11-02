<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'HARUKA', 'position' => 'リーダー', 'genre' => 'HIPHOP', 'profile' => 'チームをまとめるリーダー。力強いダンスが持ち味。', 'sort_order' => 1],
            ['name' => 'MIO',    'position' => 'センター', 'genre' => 'JAZZ',   'profile' => 'しなやかな動きで魅せるセンター。',             'sort_order' => 2],
            ['name' => 'RIKU',   'position' => null,       'genre' => 'LOCK',   'profile' => '明るいキャラクターでステージを盛り上げる。',   'sort_order' => 3],
        ];

        foreach ($members as $member) {
            // 同じ名前のメンバーがいれば更新、いなければ作成(何回実行しても重複しない)
            Member::updateOrCreate(['name' => $member['name']], $member);
        }
    }
}