<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Application\Data\UserData;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(User $user, UserData $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor): User {
            $before = $user->load('roles')->toArray();
            $attributes = [
                'person_id' => $data->personId,
                'name' => $data->name,
                'email' => $data->email,
            ];
            if ($data->password !== null && $data->password !== '') {
                $attributes['password'] = $data->password;
            }
            $user->update($attributes);
            $user->roles()->sync($data->roleIds);
            $user->refresh()->load('roles');
            $this->audit->execute('user.updated', $user, $actor, $before, $user->toArray());

            return $user;
        });
    }
}
