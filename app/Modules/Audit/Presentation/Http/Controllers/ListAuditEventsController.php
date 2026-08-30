<?php

namespace App\Modules\Audit\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Domain\Models\AuditEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListAuditEventsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('audit.view'), 403);
        $search = trim((string) $request->string('search'));

        return Inertia::render('audit/Index', [
            'events' => AuditEvent::query()
                ->with('actor:id,name')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('resource_type', 'like', "%{$search}%")
                        ->orWhere('resource_id', 'like', "%{$search}%");
                }))
                ->latest('created_at')
                ->paginate(30)
                ->withQueryString()
                ->through(fn (AuditEvent $event) => [
                    'id' => $event->id,
                    'actor' => $event->actor?->name ?? 'Sistema',
                    'action' => $event->action,
                    'resource_type' => class_basename($event->resource_type),
                    'resource_id' => $event->resource_id,
                    'ip_address' => $event->ip_address,
                    'correlation_id' => $event->correlation_id,
                    'before_data' => $event->before_data,
                    'after_data' => $event->after_data,
                    'created_at' => $event->created_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i:s'),
                ]),
            'filters' => ['search' => $search],
        ]);
    }
}
