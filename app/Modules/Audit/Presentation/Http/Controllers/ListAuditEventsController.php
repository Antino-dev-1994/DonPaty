<?php

namespace App\Modules\Audit\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Audit\Application\AuditEventViewData;
use App\Modules\Audit\Application\Data\AuditFilters;
use App\Modules\Audit\Application\ListAuditEvents;
use App\Modules\Audit\Presentation\Http\Requests\ListAuditEventsRequest;
use Inertia\Inertia;
use Inertia\Response;

class ListAuditEventsController extends Controller
{
    public function __invoke(
        ListAuditEventsRequest $request,
        ListAuditEvents $query,
        AuditEventViewData $viewData,
    ): Response {
        $filters = AuditFilters::fromRequest($request);

        return Inertia::render('audit/Index', [
            'events' => $query->execute($filters)->through($viewData->execute(...)),
            'filters' => $filters->toArray(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
