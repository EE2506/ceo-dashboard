<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class MakeOwner extends Command
{
    protected $signature = 'dashboard:make-owner';

    protected $description = 'Create an owner using a hidden password prompt';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Display name'));
        $username = strtolower(trim((string) $this->ask('Username (letters, digits, dots, hyphens or underscores)')));
        $password = (string) $this->secret('Password (at least 12 characters)', false);
        $confirmation = (string) $this->secret('Confirm password', false);

        $validator = Validator::make(
            [
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'name' => ['required', 'string', 'max:120'],
                'username' => ['required', 'regex:/\A[a-z0-9][a-z0-9._-]{2,63}\z/', 'unique:users,username'],
                'password' => ['required', 'confirmed', Password::min(12)],
            ],
        );

        if ($validator->fails() || strlen($password) > 72) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            if (strlen($password) > 72) {
                $this->error('The password must be at most 72 bytes.');
            }

            return self::FAILURE;
        }

        $owner = new User(['name' => $name, 'username' => $username, 'password' => $password]);
        $owner->role = User::ROLE_OWNER;
        $owner->save();

        $this->info('Owner created. Sign in at /admin.');

        return self::SUCCESS;
    }
}
