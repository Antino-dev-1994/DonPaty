<?php

namespace App\Modules\Attachments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachments\Application\AttachmentAccess;
use App\Modules\Attachments\Application\AttachmentResourceType;
use App\Modules\Attachments\Application\DeleteAttachment;
use App\Modules\Attachments\Domain\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteAttachmentController extends Controller
{
    public function __invoke(Request $request, Attachment $attachment, AttachmentResourceType $types, AttachmentAccess $access, DeleteAttachment $action): RedirectResponse
    {
        $attachment->load('attachable');
        $type = $types->execute($attachment->attachable);
        abort_unless($access->canDelete($request->user(), $type, $attachment->attachable, $attachment->uploaded_by), 403);
        $action->execute($attachment, $request->user());

        return back()->with('success', 'Adjunto eliminado.');
    }
}
