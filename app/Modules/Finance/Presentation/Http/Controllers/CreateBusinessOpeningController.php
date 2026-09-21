<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateBusinessOpeningController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('finance.manage'), 403);

        return Inertia::render('finance/opening/Create', [
            'defaultDate' => now()->toDateString(),
            'defaultCashAmount' => 257300,
            'defaultNequiAmount' => 42300,
        ]);
    }
}
