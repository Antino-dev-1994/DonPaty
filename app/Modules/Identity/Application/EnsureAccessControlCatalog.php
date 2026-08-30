<?php

namespace App\Modules\Identity\Application;

use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use Illuminate\Support\Facades\DB;

class EnsureAccessControlCatalog
{
    public function execute(): void
    {
        DB::transaction(function (): void {
            foreach (config('access-control.permissions') as $name => [$group, $label]) {
                Permission::query()->updateOrCreate(
                    ['name' => $name],
                    ['group' => $group, 'label' => $label],
                );
            }

            foreach (config('access-control.roles') as $name => [$label, $description, $permissionNames]) {
                $role = Role::query()->updateOrCreate(
                    ['name' => $name],
                    ['label' => $label, 'description' => $description, 'is_system' => true],
                );

                $names = $permissionNames === '*'
                    ? array_keys(config('access-control.permissions'))
                    : $permissionNames;

                $role->permissions()->sync(
                    Permission::query()->whereIn('name', $names)->pluck('id'),
                );
            }
        });
    }
}
