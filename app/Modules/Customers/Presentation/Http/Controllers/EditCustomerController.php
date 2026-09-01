<?php

namespace App\Modules\Customers\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Pricing\Domain\Models\PriceList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditCustomerController extends Controller
{
    public function __invoke(Request $request, CustomerProfile $customer): Response
    {
        abort_unless($request->user()->hasPermission('customers.manage'), 403); $customer->load('person');
        return Inertia::render('customers/Edit', ['customer' => [...$customer->only(['id','default_price_list_id','credit_limit','default_payment_term_days','delivery_notes','is_active']), ...$customer->person->only(['name','document_type','document_number','email','phone','notes'])], 'priceLists' => PriceList::query()->where('is_active', true)->orderBy('name')->get(['id','name','is_default'])]);
    }
}
