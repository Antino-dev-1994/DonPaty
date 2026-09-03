<?php

namespace App\Modules\Attachments\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachments\Application\AttachmentAccess;
use App\Modules\Attachments\Application\AttachmentIntegrity;
use App\Modules\Attachments\Application\AttachmentResourceType;
use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Audit\Application\RecordAuditEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadAttachmentController extends Controller
{
    public function __invoke(Request $request, Attachment $attachment, AttachmentResourceType $types, AttachmentAccess $access, AttachmentIntegrity $integrity, RecordAuditEvent $audit): StreamedResponse
    {
        $attachment->load('attachable');
        $type = $types->execute($attachment->attachable);
        abort_unless($access->canView($request->user(), $type, $attachment->attachable), 403);
        if (! $integrity->verify($attachment)) {
            throw new RuntimeException('El adjunto no superó la verificación de integridad.');
        }
        $audit->execute('attachment.downloaded', $attachment, $request->user(), after: ['original_name' => $attachment->original_name, 'sha256' => $attachment->sha256]);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name, ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff']);
    }
}
