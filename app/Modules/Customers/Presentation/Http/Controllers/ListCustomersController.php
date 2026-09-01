<?php

namespace App\Modules\Customers\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListCustomersController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('customers.view'), 403);
        $search = trim((string) $request->query('search'));
        $customers = CustomerProfile::query()->with(['person:id,name,document_number,email,phone', 'defaultPriceList:id,name'])->when($search, fn ($query) => $query->whereHas('person', fn ($people) => $people->where('name','like',"%{$search}%")->orWhere('document_number','like',"%{$search}%")))->latest()->paginate(25)->withQueryString();
        return Inertia::render('customers/Index', ['customers' => $customers, 'filters' => ['search' => $search], 'canManage' => $request->user()->hasPermission('customers.manage')]);
    }
}
