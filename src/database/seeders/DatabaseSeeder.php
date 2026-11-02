<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
       public function run(): void
    {
        $this->call([
            MemberSeeder::class, // 先にメンバーを作る(ユーザーが member_id で使うため)
            UserSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
