<?php

namespace App\Modules\Identity\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\Permission;
use Illuminate\Support\Facades\DB;

class UpdateRolePermissions
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    /** @param list<string> $permissionIds */
    public function execute(Role $role, array $permissionIds, User $actor): void
    {
        if ($role->name === 'owner') {
            $permissionIds = Permission::query()->pluck('id')->all();
        }

        DB::transaction(function () use ($role, $permissionIds, $actor): void {
            $before = $role->load('permissions')->toArray();
            $role->permissions()->sync($permissionIds);
            $role->refresh()->load('permissions');
            $this->audit->execute('role.permissions_updated', $role, $actor, $before, $role->toArray());
        });
    }
}
