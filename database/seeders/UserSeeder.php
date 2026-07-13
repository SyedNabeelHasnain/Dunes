<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/users.json');
        if (!File::exists($path)) {
            // Default fallback admin user if JSON is missing
            User::updateOrCreate(
                ['email' => 'admin@dunesdiscovery.com'],
                [
                    'name' => 'Admin',
                    'password' => bcrypt('admin123'),
                ]
            );
            return;
        }

        $users = json_decode(File::get($path), true);
        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => ucfirst($u['username']),
                    'password' => $u['password'], // Preserve the existing hashed password
                ]
            );
        }
    }
}
