<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Application\RevokeUserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevokeUserSessionController extends Controller
{
    public function __invoke(Request $request, User $managedUser, string $session, RevokeUserSession $action): RedirectResponse
    {
        $this->authorize('update', $managedUser);
        $action->execute($managedUser, $request->user(), $session);

        if ($managedUser->is($request->user()) && $session === $request->session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return to_route('login')->with('success', 'La sesión actual fue revocada.');
        }

        return back()->with('success', 'Sesión revocada.');
    }
}
