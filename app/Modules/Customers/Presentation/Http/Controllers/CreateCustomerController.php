<?php

namespace App\Modules\Customers\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pricing\Domain\Models\PriceList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateCustomerController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('customers.manage'), 403);
        return Inertia::render('customers/Create', ['priceLists' => PriceList::query()->where('is_active', true)->orderBy('name')->get(['id','name','is_default'])]);
    }
}
