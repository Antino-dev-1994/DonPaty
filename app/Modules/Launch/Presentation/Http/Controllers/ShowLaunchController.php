<?php

namespace App\Modules\Launch\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Launch\Application\LaunchConfigurationLocator;
use App\Modules\Launch\Application\LaunchReadinessQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowLaunchController extends Controller
{
    public function __invoke(Request $request, LaunchConfigurationLocator $locator, LaunchReadinessQuery $readiness): Response
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);
        $configuration = $locator->execute()->load('activator:id,name');

        return Inertia::render('launch/Index', [
            'configuration' => [
                'status' => $configuration->status,
                'cutoff_at' => $configuration->cutoff_at?->format('Y-m-d\TH:i'),
                'attestations' => $configuration->attestations ?? [],
                'notes' => $configuration->notes,
                'activated_at' => $configuration->activated_at?->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'activated_by' => $configuration->activator?->name,
            ],
            'readiness' => $readiness->execute($configuration),
        ]);
    }
}
