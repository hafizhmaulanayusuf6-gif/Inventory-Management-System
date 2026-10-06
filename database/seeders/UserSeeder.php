<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@gudang.test',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Kepala Gudang',
                'email' => 'kepala@gudang.test',
                'role' => 'Kepala Gudang',
            ],
            [
                'name' => 'Staff Gudang',
                'email' => 'staff@gudang.test',
                'role' => 'Staff Gudang',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password', // di-hash otomatis oleh cast 'hashed'
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}