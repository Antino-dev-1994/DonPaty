<?php

namespace App\Modules\Shared\Application;

use App\Modules\Shared\Domain\Models\DocumentSequence;
use Illuminate\Support\Carbon;

class NextDocumentNumber
{
    public function execute(string $type, string $prefix, ?Carbon $date = null): string
    {
        $period = ($date ?? now())->format('Ym');
        $sequence = DocumentSequence::query()->firstOrCreate(
            ['document_type' => $type, 'period' => $period],
            ['last_number' => 0],
        );
        $sequence = DocumentSequence::query()->lockForUpdate()->findOrFail($sequence->id);
        $sequence->increment('last_number');

        return sprintf('%s-%s-%05d', $prefix, $period, $sequence->last_number);
    }
}
