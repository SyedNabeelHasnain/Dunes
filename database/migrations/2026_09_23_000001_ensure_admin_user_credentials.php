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

        // 1. Primary admin account (supports username 'admin' and email 'dunesdiscovery85@gmail.com')
        $primaryAdmin = User::where('id', 1)
            ->orWhere('email', 'dunesdiscovery85@gmail.com')
            ->orWhereRaw('LOWER(name) = ?', ['admin'])
            ->first();

        if ($primaryAdmin) {
            $primaryAdmin->name = 'admin';
            $primaryAdmin->email = 'dunesdiscovery85@gmail.com';
            $primaryAdmin->password = Hash::make('admin123');
            $primaryAdmin->email_verified_at = $primaryAdmin->email_verified_at ?? now();
            $primaryAdmin->save();
        } else {
            User::create([
                'id' => 1,
                'name' => 'admin',
                'email' => 'dunesdiscovery85@gmail.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
        }

        // 2. Secondary domain admin (so admin@dunesdiscoverytourism.com also authenticates)
        $domainAdmin = User::where('email', 'admin@dunesdiscoverytourism.com')->first();
        if (! $domainAdmin) {
            User::create([
                'name' => 'Admin Concierge',
                'email' => 'admin@dunesdiscoverytourism.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
        } else {
            $domainAdmin->password = Hash::make('admin123');
            $domainAdmin->save();
        }

        // 3. Fallback for legacy admin@dunesdiscovery.com if present
        $legacyAdmin = User::where('email', 'admin@dunesdiscovery.com')->first();
        if ($legacyAdmin) {
            $legacyAdmin->password = Hash::make('admin123');
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
