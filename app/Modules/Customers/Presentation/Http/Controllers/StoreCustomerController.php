<?php

namespace App\Modules\Customers\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Application\Data\CustomerData;
use App\Modules\Customers\Application\RegisterCustomer;
use App\Modules\Customers\Presentation\Http\Requests\SaveCustomerRequest;
use Illuminate\Http\RedirectResponse;

class StoreCustomerController extends Controller
{
    public function __invoke(SaveCustomerRequest $request, RegisterCustomer $action): RedirectResponse
    {
        $customer = $action->execute(CustomerData::fromArray($request->validated()));
        return to_route('customers.edit', $customer)->with('success', 'Cliente creado correctamente.');
    }
}
