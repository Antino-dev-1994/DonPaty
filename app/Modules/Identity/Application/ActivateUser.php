<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Enums\UserStatus;

class ActivateUser
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(User $user, User $actor): void
    {
        $before = $user->only(['status', 'blocked_at', 'blocked_reason']);
        $user->update([
            'status' => UserStatus::Active,
            'blocked_at' => null,
            'blocked_reason' => null,
        ]);
        $this->audit->execute('user.activated', $user, $actor, $before, $user->only(['status', 'blocked_at', 'blocked_reason']));
    }
}
