<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditRoleController extends Controller
{
    public function __invoke(Request $request, Role $role): Response
    {
        abort_unless($request->user()->hasPermission('roles.manage'), 403);
        $role->load('permissions');

        return Inertia::render('identity/roles/Edit', [
            'role' => [
                ...$role->only(['id', 'name', 'label', 'description']),
                'permissions' => $role->permissions->pluck('id'),
            ],
            'permissionGroups' => Permission::query()->orderBy('group')->orderBy('label')->get()
                ->groupBy('group')
                ->map(fn ($permissions, $group) => ['group' => $group, 'permissions' => $permissions->values()])
                ->values(),
        ]);
    }
}
