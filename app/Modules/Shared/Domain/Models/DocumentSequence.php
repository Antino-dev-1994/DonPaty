<?php

namespace App\Modules\Shared\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['document_type', 'period', 'last_number'])]
class DocumentSequence extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return ['last_number' => 'integer'];
    }
}
