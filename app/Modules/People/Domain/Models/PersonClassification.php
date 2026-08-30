<?php

namespace App\Modules\People\Domain\Models;

use App\Modules\People\Domain\Enums\PersonClassificationType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['person_id', 'classification', 'effective_from', 'effective_to'])]
class PersonClassification extends Model
{
    use HasUlids;

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    protected function casts(): array
    {
        return [
            'classification' => PersonClassificationType::class,
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }
}
