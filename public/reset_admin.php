<?php

if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die("Unauthorized.");
}

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/../dunes-laravel/vendor/autoload.php';
$app = require_once __DIR__.'/../dunes-laravel/bootstrap/app.php';

// Bootstrap via Console Kernel to load DB & Eloquent without routing
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::where('email', 'admin@dunesdiscovery.com')->first();
    if ($user) {
        $user->password = Hash::make('AdminDunes2026!');
        $user->save();
        echo "SUCCESS: Admin password has been reset to: AdminDunes2026!\n";
    } else {
        // Create user if not exists
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@dunesdiscovery.com',
            'password' => Hash::make('AdminDunes2026!')
        ]);
        echo "SUCCESS: Admin user did not exist, so it has been created with password: AdminDunes2026!\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Self-destruct
unlink(__FILE__);
