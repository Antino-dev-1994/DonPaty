<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Application\Data\UserData;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(UserData $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor): User {
            $user = User::create([
                'person_id' => $data->personId,
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
            ]);
            $user->roles()->sync($data->roleIds);
            $this->audit->execute('user.created', $user, $actor, after: $user->load('roles')->toArray());

            return $user;
        });
    }
}
