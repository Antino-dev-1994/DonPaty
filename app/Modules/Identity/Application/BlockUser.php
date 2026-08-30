<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Enums\UserStatus;
use Illuminate\Support\Facades\DB;

class BlockUser
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(User $user, User $actor, string $reason): void
    {
        DB::transaction(function () use ($user, $actor, $reason): void {
            $before = $user->only(['status', 'blocked_at', 'blocked_reason']);
            $user->forceFill([
                'status' => UserStatus::Blocked,
                'blocked_at' => now(),
                'blocked_reason' => $reason,
                'remember_token' => null,
            ])->save();
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->getKey())->delete();
            $this->audit->execute('user.blocked', $user, $actor, $before, $user->only(['status', 'blocked_at', 'blocked_reason']));
        });
    }
}
