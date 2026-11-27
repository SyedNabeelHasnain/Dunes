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
                            {login : The administrator username or email address}
                            {password : The new password to assign (minimum 10 characters)}
                            {--create : Create the administrator account if it does not already exist}';

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

        if (empty($login) || strlen($password) < 10) {
            $this->error('Both login and a password of at least 10 characters are required.');

            return self::FAILURE;
        }

        $user = User::where('email', $login)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($login)])
            ->first();

        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
            $this->info("Successfully updated password for administrator [{$user->name} / {$user->email}].");
        } elseif ($this->option('create')) {
            $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
            $user = User::create([
                'name' => $isEmail ? 'admin' : $login,
                'email' => $isEmail ? $login : 'admin@dunesdiscoverytourism.com',
                'password' => Hash::make($password),
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $this->info("Created new administrator account [{$user->name} / {$user->email}] with the specified password.");
        } else {
            $this->error("Administrator [{$login}] not found. Re-run with --create to provision a new account.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
