<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use Illuminate\Support\Facades\DB;

class RevokeUserSession
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(User $user, User $actor, string $sessionId): bool
    {
        $deleted = DB::table(config('session.table', 'sessions'))
            ->where('id', $sessionId)
            ->where('user_id', $user->getKey())
            ->delete() > 0;

        if ($deleted) {
            $this->audit->execute('user.session_revoked', $user, $actor, after: ['session_id' => $sessionId]);
        }

        return $deleted;
    }
}
