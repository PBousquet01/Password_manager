<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Test Student',
                'password' => 'StrongPass123!',
            ],
        );
    }
}
