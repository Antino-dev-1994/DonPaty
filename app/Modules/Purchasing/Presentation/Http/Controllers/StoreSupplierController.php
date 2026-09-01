<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Application\RegisterSupplier;
use App\Modules\Purchasing\Presentation\Http\Requests\SaveSupplierRequest;
use Illuminate\Http\RedirectResponse;

class StoreSupplierController extends Controller
{
    public function __invoke(SaveSupplierRequest $request, RegisterSupplier $action): RedirectResponse
    {
        $supplier = $action->execute(SupplierData::fromArray($request->validated()));

        return to_route('purchasing.suppliers.edit', $supplier)->with('success', 'Proveedor creado correctamente.');
    }
}
