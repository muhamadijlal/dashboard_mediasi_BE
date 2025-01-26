<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        $users = [
            [
                'name' => "Development",
                'email' => "dev@dev.com",
                'password' => bcrypt('password'),
            ],
            [
                'name' => "IT DEV",
                'email' => "itdev@jmto.com",
                'password' => bcrypt('password'),
            ],
            [
                'name' => "user",
                'email' => "user@mail.com",
                'password' => bcrypt('password'),
            ]
        ];

        User::insert($users);
    }
}
