<?php

namespace App\Modules\Dashboard\Application;

use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Finance\Domain\Models\Payable;
use App\Modules\Sales\Domain\Models\Receivable;

class ObligationsQuery
{
    /** @return array<string, mixed> */
    public function execute(?string $personId = null): array
    {
        $today = today();
        $receivables = Receivable::query()->with('person:id,name')->whereIn('status', ['pending', 'partial'])->when($personId, fn ($query, $id) => $query->where('person_id', $id))->orderBy('due_at')->get();
        $payables = Payable::query()->with('person:id,name')->whereIn('status', ['pending', 'partial'])->when($personId, fn ($query, $id) => $query->where('person_id', $id))->orderBy('due_at')->get();

        return [
            'receivables_total' => (int) $receivables->sum('balance_amount'),
            'receivables_overdue' => (int) $receivables->filter(fn ($item) => $item->due_at?->lt($today))->sum('balance_amount'),
            'payables_total' => (int) $payables->sum('balance_amount'),
            'payables_overdue' => (int) $payables->filter(fn ($item) => $item->due_at?->lt($today))->sum('balance_amount'),
            'receivables' => $receivables->map(fn ($item) => $this->row($item))->all(),
            'payables' => $payables->map(fn ($item) => $this->row($item))->all(),
        ];
    }

    /** @return array<string, int|string|bool|null> */
    private function row($item): array
    {
        return [
            'id' => $item->id,
            'document' => $item->document_number,
            'person' => $item->person->name,
            'due_at' => $item->due_at?->format('Y-m-d'),
            'balance' => $item->balance_amount,
            'is_overdue' => $item->due_at?->isPast() ?? false,
        ];
    }
}
