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
        $envPassword = (string) env('ADMIN_PASSWORD', '');
        if (strlen($envPassword) >= 12) {
            $defaultPassword = $envPassword;
        } elseif (app()->environment(['local', 'testing'])) {
            $defaultPassword = $envPassword !== '' ? $envPassword : 'admin123';
        } else {
            // Never seed a weak default password in production when ADMIN_PASSWORD is not securely configured
            $defaultPassword = \Illuminate\Support\Str::password(24);
        }

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

                    $user = User::firstOrNew(['id' => $u['id']]);
                    $user->forceFill([
                        'id' => $u['id'],
                        'name' => ucfirst($u['username']),
                        'email' => $u['email'],
                        'password' => $pass,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'role' => $user->role ?: 'super_admin',
                        'is_active' => $user->is_active ?? true,
                    ])->save();
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
            $existing = User::where('email', $acc['email'])->first();
            if (! $existing) {
                $created = new User();
                $created->forceFill([
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                    'role' => 'super_admin',
                    'is_active' => true,
                ])->save();
            }
        }
    }
}
