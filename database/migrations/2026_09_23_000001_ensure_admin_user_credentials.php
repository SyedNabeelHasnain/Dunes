<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to guarantee functional, verified admin credentials across environments.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $envPassword = (string) env('ADMIN_PASSWORD', '');
        $defaultPassword = null;
        if (strlen($envPassword) >= 12) {
            $defaultPassword = $envPassword;
        } elseif (app()->environment(['local', 'testing'])) {
            $defaultPassword = $envPassword !== '' ? $envPassword : 'admin123';
        }

        // 1. Primary admin account (supports username 'admin' and email 'dunesdiscovery85@gmail.com')
        $primaryAdmin = User::where('id', 1)
            ->orWhere('email', 'dunesdiscovery85@gmail.com')
            ->orWhereRaw('LOWER(name) = ?', ['admin'])
            ->first();

        if ($primaryAdmin) {
            $primaryAdmin->name = $primaryAdmin->name ?: 'admin';
            $primaryAdmin->email = $primaryAdmin->email ?: 'dunesdiscovery85@gmail.com';
            if (empty($primaryAdmin->password) && $defaultPassword !== null) {
                $primaryAdmin->password = Hash::make($defaultPassword);
            }
            $primaryAdmin->forceFill([
                'email_verified_at' => $primaryAdmin->email_verified_at ?? now(),
            ]);
            $primaryAdmin->save();
        } elseif ($defaultPassword !== null) {
            $admin = new User();
            $admin->forceFill([
                'id' => 1,
                'name' => 'admin',
                'email' => 'dunesdiscovery85@gmail.com',
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);
            if (Schema::hasColumn('users', 'role')) {
                $admin->role = 'super_admin';
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $admin->is_active = true;
            }
            $admin->save();
        }

        // 2. Secondary domain admin (so admin@dunesdiscoverytourism.com also authenticates)
        $domainAdmin = User::where('email', 'admin@dunesdiscoverytourism.com')->first();
        if (! $domainAdmin && $defaultPassword !== null) {
            $secondary = new User();
            $secondary->forceFill([
                'name' => 'Admin Concierge',
                'email' => 'admin@dunesdiscoverytourism.com',
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);
            if (Schema::hasColumn('users', 'role')) {
                $secondary->role = 'super_admin';
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $secondary->is_active = true;
            }
            $secondary->save();
        } elseif ($domainAdmin && empty($domainAdmin->password) && $defaultPassword !== null) {
            $domainAdmin->password = Hash::make($defaultPassword);
            $domainAdmin->save();
        }

        // 3. Fallback for legacy admin@dunesdiscovery.com if present
        $legacyAdmin = User::where('email', 'admin@dunesdiscovery.com')->first();
        if ($legacyAdmin && empty($legacyAdmin->password) && $defaultPassword !== null) {
            $legacyAdmin->password = Hash::make($defaultPassword);
            $legacyAdmin->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve operator data integrity
    }
};
