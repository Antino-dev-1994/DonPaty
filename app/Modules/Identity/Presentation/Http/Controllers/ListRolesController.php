<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListRolesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('roles.manage'), 403);

        return Inertia::render('identity/roles/Index', [
            'roles' => Role::query()->withCount(['users', 'permissions'])->orderBy('label')->get(),
        ]);
    }
}
