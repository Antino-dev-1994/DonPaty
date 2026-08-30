<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListAuthorizationsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('authorizations.approve') || $request->user()->hasPermission('authorizations.request'), 403);

        $authorizations = AuthorizationRequest::query()
            ->with(['requester:id,name', 'approver:id,name'])
            ->when(! $request->user()->hasPermission('authorizations.approve'), fn ($query) => $query->where('requested_by', $request->user()->id))
            ->latest()
            ->paginate(25)
            ->through(fn (AuthorizationRequest $authorization) => [
                'id' => $authorization->id,
                'operation_type' => $authorization->operation_type,
                'reason' => $authorization->reason,
                'status' => $authorization->status->value,
                'status_label' => $authorization->status->label(),
                'requester' => $authorization->requester->name,
                'approver' => $authorization->approver?->name,
                'decision_notes' => $authorization->decision_notes,
                'expires_at' => $authorization->expires_at?->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'created_at' => $authorization->created_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'can_decide' => $authorization->status === AuthorizationStatus::Pending
                    && $authorization->requested_by !== $request->user()->id
                    && $request->user()->hasPermission($authorization->approval_permission),
            ]);

        return Inertia::render('identity/authorizations/Index', ['authorizations' => $authorizations]);
    }
}
