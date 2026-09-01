<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Application\UpdateSupplier;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use App\Modules\Purchasing\Presentation\Http\Requests\SaveSupplierRequest;
use Illuminate\Http\RedirectResponse;

class UpdateSupplierController extends Controller
{
    public function __invoke(SaveSupplierRequest $request, SupplierProfile $supplier, UpdateSupplier $action): RedirectResponse
    {
        $action->execute($supplier, SupplierData::fromArray($request->validated()));

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }
}
