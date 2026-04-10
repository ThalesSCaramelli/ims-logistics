<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:set-password
                            {--email=admin@ims.com.au : The email address of the admin user}
                            {--password=admin123 : The new password to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the password for an admin user (defaults to admin@ims.com.au / admin123)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email    = $this->option('email');
        $password = $this->option('password');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");

            return self::FAILURE;
        }

        $user->password = $password;
        $user->save();

        $this->info("Password updated successfully for {$email}.");

        return self::SUCCESS;
    }
}
