<?php

namespace App\Modules\Customers\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Sales\Domain\Enums\ReceivableStatus;
use App\Modules\Sales\Domain\Models\Receivable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowCustomerController extends Controller
{
    public function __invoke(Request $request, CustomerProfile $customer): Response
    {
        abort_unless($request->user()->hasPermission('customers.view'), 403);

        $customer->load(['person:id,name,document_number,email,phone', 'defaultPriceList:id,name']);
        $receivables = Receivable::query()
            ->where('person_id', $customer->person_id)
            ->latest('issued_at')
            ->limit(50)
            ->get();
        $openBalance = (int) $receivables
            ->whereIn('status', [ReceivableStatus::Pending, ReceivableStatus::Partial])
            ->sum('balance_amount');

        return Inertia::render('customers/Show', [
            'customer' => [
                ...$customer->only(['id', 'credit_limit', 'default_payment_term_days', 'delivery_notes', 'is_active']),
                'name' => $customer->person->name,
                'document_number' => $customer->person->document_number,
                'email' => $customer->person->email,
                'phone' => $customer->person->phone,
                'price_list' => $customer->defaultPriceList?->name,
                'open_balance' => $openBalance,
                'available_credit' => max(0, $customer->credit_limit - $openBalance),
                'receivables' => $receivables->map(fn (Receivable $receivable) => [
                    ...$receivable->only(['id', 'document_number', 'original_amount', 'paid_amount', 'balance_amount']),
                    'issued_at' => $receivable->issued_at->format('Y-m-d'),
                    'due_at' => $receivable->due_at?->format('Y-m-d'),
                    'status' => $receivable->status->value,
                    'status_label' => $this->statusLabel($receivable->status),
                    'is_overdue' => $receivable->due_at?->isPast() && in_array($receivable->status, [ReceivableStatus::Pending, ReceivableStatus::Partial], true),
                ]),
            ],
            'canManage' => $request->user()->hasPermission('customers.manage'),
            'canReceivePayments' => $request->user()->hasPermission('receivables.manage'),
        ]);
    }

    private function statusLabel(ReceivableStatus $status): string
    {
        return match ($status) {
            ReceivableStatus::Pending => 'Pendiente',
            ReceivableStatus::Partial => 'Abono parcial',
            ReceivableStatus::Paid => 'Pagado',
            ReceivableStatus::Cancelled => 'Cancelado',
        };
    }
}
