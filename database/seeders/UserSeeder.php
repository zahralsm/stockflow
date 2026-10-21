<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->error(
                'Seeder akun demo hanya boleh dijalankan di local atau testing.'
            );

            return;
        }

        $accounts = [
            [
                'name' => 'Admin StockFlow',
                'username' => 'admin',
                'email' => 'admin@stockflow.test',
                'password' => 'admin123',
                'role' => 'admin',
            ],
            [
                'name' => 'Staff StockFlow',
                'username' => 'staff',
                'email' => 'staff@stockflow.test',
                'password' => 'staff123',
                'role' => 'staff',
            ],
        ];

        DB::transaction(function () use ($accounts) {
            foreach ($accounts as $account) {
                $user = User::firstOrNew([
                    'email' => $account['email'],
                ]);

                $user->forceFill([
                    'name' => $account['name'],
                    'username' => $account['username'],
                    'password' => $account['password'],
                    'role' => $account['role'],
                    'email_verified_at' => now(),
                ]);

                $user->save();
            }
        });

        $this->command->info(
            'Akun demo admin dan staff berhasil disiapkan.'
        );
    }
}