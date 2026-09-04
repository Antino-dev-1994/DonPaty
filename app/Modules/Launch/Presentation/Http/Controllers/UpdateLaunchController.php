<?php

namespace App\Modules\Launch\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Launch\Application\LaunchConfigurationLocator;
use App\Modules\Launch\Application\SaveLaunchConfiguration;
use App\Modules\Launch\Presentation\Http\Requests\SaveLaunchConfigurationRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;

class UpdateLaunchController extends Controller
{
    public function __invoke(SaveLaunchConfigurationRequest $request, LaunchConfigurationLocator $locator, SaveLaunchConfiguration $save): RedirectResponse
    {
        try {
            $save->execute($locator->execute(), $request->validated(), $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['launch' => $exception->getMessage()]);
        }

        return back()->with('success', 'Preparación de lanzamiento guardada.');
    }
}
