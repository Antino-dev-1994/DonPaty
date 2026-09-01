<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateSupplierController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);

        return Inertia::render('purchasing/suppliers/Create');
    }
}
