<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListSuppliersController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);
        $search = trim($request->string('search')->toString());

        return Inertia::render('purchasing/suppliers/Index', [
            'suppliers' => SupplierProfile::query()->with('person')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('trade_name', 'like', "%{$search}%")
                        ->orWhere('tax_identifier', 'like', "%{$search}%")
                        ->orWhereHas('person', fn ($query) => $query->where('name', 'like', "%{$search}%")
                            ->orWhere('document_number', 'like', "%{$search}%"));
                }))
                ->orderByDesc('is_active')->orderBy('trade_name')->paginate(25)->withQueryString()
                ->through(fn (SupplierProfile $supplier) => [
                    ...$supplier->only(['id', 'trade_name', 'tax_identifier', 'default_payment_term_days', 'is_active']),
                    'person' => $supplier->person->only(['name', 'document_number', 'email', 'phone']),
                ]),
            'filters' => ['search' => $search],
        ]);
    }
}
