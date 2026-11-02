<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ダンサー(メンバー名とメールアドレスの組み合わせ)
        $dancers = [
            'HARUKA' => 'haruka@example.com',
            'MIO'    => 'mio@example.com',
            'RIKU'   => 'riku@example.com',
        ];

        foreach ($dancers as $memberName => $email) {
            $member = Member::where('name', $memberName)->firstOrFail();

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $memberName,
                    'password'          => 'password', // User の casts で自動的にハッシュ化される
                    'role'              => UserRole::Dancer,
                    'member_id'         => $member->id,
                    'email_verified_at' => now(),
                ]
            );
        }

        // ファン
        User::updateOrCreate(
            ['email' => 'fan@example.com'],
            [
                'name'              => 'ファン太郎',
                'password'          => 'password',
                'role'              => UserRole::Fan,
                'member_id'         => null,
                'email_verified_at' => now(),
            ]
        );
    }
}