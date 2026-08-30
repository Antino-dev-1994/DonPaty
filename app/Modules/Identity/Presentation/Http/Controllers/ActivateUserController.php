<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Application\ActivateUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivateUserController extends Controller
{
    public function __invoke(Request $request, User $managedUser, ActivateUser $action): RedirectResponse
    {
        $this->authorize('update', $managedUser);
        $action->execute($managedUser, $request->user());

        return back()->with('success', 'Usuario activado correctamente.');
    }
}
