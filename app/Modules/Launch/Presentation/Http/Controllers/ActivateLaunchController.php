<?php

namespace App\Modules\Launch\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Launch\Application\ActivateLaunch;
use App\Modules\Launch\Application\LaunchConfigurationLocator;
use App\Modules\Launch\Presentation\Http\Requests\ActivateLaunchRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ActivateLaunchController extends Controller
{
    public function __invoke(ActivateLaunchRequest $request, LaunchConfigurationLocator $locator, ActivateLaunch $activate): RedirectResponse
    {
        try {
            $activate->execute($locator->execute(), $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['launch' => $exception->getMessage()]);
        }

        return back()->with('success', 'Fecha de corte activada. DonPaty inició operación oficial.');
    }
}
