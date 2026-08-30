<?php

namespace App\Modules\People\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListPeopleController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', Person::class);
        $search = trim((string) $request->string('search'));

        $people = Person::query()
            ->with(['classifications' => fn ($query) => $query->whereNull('effective_to'), 'user:id,person_id,status'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Person $person) => [
                'id' => $person->id,
                'name' => $person->name,
                'document' => collect([$person->document_type, $person->document_number])->filter()->join(' '),
                'email' => $person->email,
                'phone' => $person->phone,
                'is_active' => $person->is_active,
                'has_user' => $person->user !== null,
                'classifications' => $person->classifications->map(fn ($item) => [
                    'value' => $item->classification->value,
                    'label' => $item->classification->label(),
                ])->values(),
            ]);

        return Inertia::render('people/Index', [
            'people' => $people,
            'filters' => ['search' => $search],
            'canManage' => $request->user()->can('create', Person::class),
        ]);
    }
}
