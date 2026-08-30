<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use Illuminate\Support\Facades\Hash;

class AuthenticateUser
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    /** @param array<string, mixed> $credentials */
    public function execute(array $credentials): ?User
    {
        $user = User::query()->where('email', $credentials['email'] ?? null)->first();

        if (! $user || ! $user->isActive() || ! Hash::check((string) ($credentials['password'] ?? ''), $user->password)) {
            return null;
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $this->audit->execute('user.logged_in', $user, $user);

        return $user;
    }
}
