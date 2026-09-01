<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditSupplierController extends Controller
{
    public function __invoke(Request $request, SupplierProfile $supplier): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);
        $supplier->load('person');

        return Inertia::render('purchasing/suppliers/Edit', [
            'supplier' => [
                ...$supplier->only(['id', 'trade_name', 'tax_identifier', 'default_payment_term_days', 'notes', 'is_active']),
                ...$supplier->person->only(['name', 'document_type', 'document_number', 'email', 'phone']),
            ],
        ]);
    }
}
