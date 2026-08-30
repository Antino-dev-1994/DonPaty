<?php

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

final class ShowDashboardController
{
    public function __invoke(): Response
    {
        return Inertia::render('dashboard/Index');
    }
}
