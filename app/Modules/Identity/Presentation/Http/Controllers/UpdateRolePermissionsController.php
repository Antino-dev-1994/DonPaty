<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\UpdateRolePermissions;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Presentation\Http\Requests\UpdateRolePermissionsRequest;
use Illuminate\Http\RedirectResponse;

class UpdateRolePermissionsController extends Controller
{
    public function __invoke(UpdateRolePermissionsRequest $request, Role $role, UpdateRolePermissions $action): RedirectResponse
    {
        $action->execute($role, $request->validated('permissions'), $request->user());

        return back()->with('success', 'Permisos del rol actualizados.');
    }
}
