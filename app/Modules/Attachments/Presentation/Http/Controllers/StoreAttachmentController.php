<?php

namespace App\Modules\Attachments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachments\Application\AttachmentAccess;
use App\Modules\Attachments\Application\ResolveAttachmentResource;
use App\Modules\Attachments\Application\StoreAttachment;
use App\Modules\Attachments\Presentation\Http\Requests\StoreAttachmentRequest;
use Illuminate\Http\RedirectResponse;

class StoreAttachmentController extends Controller
{
    public function __invoke(string $type, string $id, StoreAttachmentRequest $request, ResolveAttachmentResource $resources, AttachmentAccess $access, StoreAttachment $action): RedirectResponse
    {
        $resource = $resources->execute($type, $id);
        abort_unless($access->canUpload($request->user(), $type, $resource), 403);
        $action->execute($resource, $request->file('file'), $request->user());

        return back()->with('success', 'Adjunto guardado de forma privada.');
    }
}
