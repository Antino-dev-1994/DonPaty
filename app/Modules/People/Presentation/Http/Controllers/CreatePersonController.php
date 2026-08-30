<?php

namespace App\Modules\People\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\Person;
use Inertia\Inertia;
use Inertia\Response;

class CreatePersonController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('create', Person::class);

        return Inertia::render('people/Create', [
            'classificationOptions' => collect(PersonClassificationType::cases())
                ->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()]),
        ]);
    }
}
