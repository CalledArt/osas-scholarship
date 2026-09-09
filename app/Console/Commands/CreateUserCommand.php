<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command
{
    protected $signature = 'users:create';

    protected $description = 'Create a new user account (student or OSAS admin)';

    public function handle(): int
    {
        $name = text(
            label: 'Full name',
            required: true,
        );

        $email = text(
            label: 'Email address',
            required: true,
            validate: fn (string $value) => match (true) {
                ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'Enter a valid email address.',
                User::where('email', $value)->exists() => 'A user with this email already exists.',
                default => null,
            },
        );

        $role = select(
            label: 'Role',
            options: [
                UserRole::Student->value => UserRole::Student->label(),
                UserRole::Admin->value => UserRole::Admin->label(),
            ],
            default: UserRole::Student->value,
        );

        $plainPassword = password(
            label: 'Temporary password',
            required: true,
            validate: fn (string $value) => Password::min(8)->validate($value) ? null : 'Password must be at least 8 characters.',
        );

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'role' => UserRole::from($role),
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        $this->newLine();
        $this->components->info("Account created for {$user->name} ({$user->email}) as {$user->role->label()}.");

        return self::SUCCESS;
    }
}
