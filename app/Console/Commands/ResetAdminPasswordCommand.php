<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password 
                            {login=admin : The administrator username or email address} 
                            {password=admin123 : The new password to assign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set or reset the password for an administrator account by username or email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $login = trim((string) $this->argument('login'));
        $password = (string) $this->argument('password');

        if (empty($login) || empty($password)) {
            $this->error('Both login and password are required.');
            return self::FAILURE;
        }

        $user = User::where('email', $login)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($login)])
            ->first();

        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
            $this->info("Successfully updated password for administrator [{$user->name} / {$user->email}].");
        } else {
            $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
            $user = User::create([
                'name' => $isEmail ? 'admin' : $login,
                'email' => $isEmail ? $login : 'admin@dunesdiscoverytourism.com',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("Created new administrator account [{$user->name} / {$user->email}] with the specified password.");
        }

        return self::SUCCESS;
    }
}
