<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = env('ADMIN_PASSWORD', 'admin123');

        $path = database_path('seeders/data/users.json');
        if (File::exists($path)) {
            $users = json_decode(File::get($path), true);
            if (is_array($users)) {
                foreach ($users as $u) {
                    $pass = $u['password'] ?? $defaultPassword;
                    if (str_starts_with($pass, '$2a$')) {
                        $pass = '$2y$'.substr($pass, 4);
                    }

                    // Guard against invalid hashes or configuration mismatches across PHP/environment versions
                    if (! Hash::isHashed($pass) || ! Hash::verifyConfiguration($pass)) {
                        $pass = $defaultPassword;
                    }

                    User::updateOrCreate(
                        ['id' => $u['id']],
                        [
                            'name' => ucfirst($u['username']),
                            'email' => $u['email'],
                            'password' => $pass,
                            'email_verified_at' => now(),
                        ]
                    );
                }
            }
        }

        // Guarantee all standard administrative aliases exist and have verified status
        $adminAccounts = [
            ['email' => 'admin@dunesdiscoverytourism.com', 'name' => 'Admin'],
            ['email' => 'admin@dunesdiscovery.com', 'name' => 'Admin'],
            ['email' => 'dunesdiscovery85@gmail.com', 'name' => 'Admin'],
        ];

        foreach ($adminAccounts as $acc) {
            User::firstOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}

