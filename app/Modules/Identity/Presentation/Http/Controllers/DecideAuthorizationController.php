<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\DecideAuthorizationRequest;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Identity\Presentation\Http\Requests\DecideAuthorizationRequestRequest;
use Illuminate\Http\RedirectResponse;

class DecideAuthorizationController extends Controller
{
    public function __invoke(DecideAuthorizationRequestRequest $request, AuthorizationRequest $authorization, DecideAuthorizationRequest $action): RedirectResponse
    {
        $action->execute(
            $authorization,
            $request->user(),
            AuthorizationStatus::from($request->validated('decision')),
            $request->validated('notes'),
        );

        return back()->with('success', 'Solicitud decidida correctamente.');
    }
}
