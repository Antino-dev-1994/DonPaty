<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Application\BlockUser;
use App\Modules\Identity\Presentation\Http\Requests\BlockUserRequest;
use Illuminate\Http\RedirectResponse;

class BlockUserController extends Controller
{
    public function __invoke(BlockUserRequest $request, User $managedUser, BlockUser $action): RedirectResponse
    {
        $action->execute($managedUser, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Usuario bloqueado y sesiones revocadas.');
    }
}
