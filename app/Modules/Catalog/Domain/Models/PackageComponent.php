<?php

namespace App\Modules\Catalog\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['package_presentation_id', 'component_presentation_id', 'quantity'])]
class PackageComponent extends Model
{
    use HasUlids;

    public function packagePresentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class, 'package_presentation_id');
    }

    public function componentPresentation(): BelongsTo
    {
        return $this->belongsTo(ProductPresentation::class, 'component_presentation_id');
    }

    protected function casts(): array
    {
        return ['quantity' => 'decimal:6'];
    }
}
