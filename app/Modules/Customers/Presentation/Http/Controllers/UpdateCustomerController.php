<?php

namespace App\Modules\Customers\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Application\Data\CustomerData;
use App\Modules\Customers\Application\UpdateCustomer;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Customers\Presentation\Http\Requests\SaveCustomerRequest;
use Illuminate\Http\RedirectResponse;

class UpdateCustomerController extends Controller
{
    public function __invoke(SaveCustomerRequest $request, CustomerProfile $customer, UpdateCustomer $action): RedirectResponse
    {
        $action->execute($customer, CustomerData::fromArray($request->validated()));
        return back()->with('success', 'Cliente actualizado correctamente.');
    }
}
