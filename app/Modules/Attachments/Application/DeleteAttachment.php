<?php

namespace App\Modules\Attachments\Application;

use App\Models\User;
use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Audit\Application\RecordAuditEvent;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteAttachment
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(Attachment $attachment, User $actor): void
    {
        if (! Storage::disk($attachment->disk)->delete($attachment->path)) {
            throw new RuntimeException('No fue posible eliminar el archivo físico.');
        }
        $this->audit->execute('attachment.deleted', $attachment, $actor, before: $attachment->only(['attachable_type', 'attachable_id', 'original_name', 'mime_type', 'size', 'sha256']));
        $attachment->delete();
    }
}
