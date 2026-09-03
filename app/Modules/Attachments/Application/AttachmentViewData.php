<?php

namespace App\Modules\Attachments\Application;

use App\Models\User;
use App\Modules\Attachments\Domain\Models\Attachment;
use Illuminate\Database\Eloquent\Model;

class AttachmentViewData
{
    public function __construct(private readonly AttachmentAccess $access) {}

    /** @return array{resource_type:string,resource_id:string,can_upload:bool,items:list<array<string, mixed>>} */
    public function execute(string $type, Model $resource, User $viewer): array
    {
        abort_unless($this->access->canView($viewer, $type, $resource), 403);
        $attachments = Attachment::query()->with('uploader:id,name')
            ->where('attachable_type', $resource->getMorphClass())
            ->where('attachable_id', $resource->getKey())
            ->latest('created_at')->get();

        return [
            'resource_type' => $type,
            'resource_id' => (string) $resource->getKey(),
            'can_upload' => $this->access->canUpload($viewer, $type, $resource),
            'items' => $attachments->map(fn (Attachment $attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'sha256' => $attachment->sha256,
                'uploaded_by' => $attachment->uploader->name,
                'created_at' => $attachment->created_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'download_url' => route('attachments.download', $attachment),
                'can_delete' => $this->access->canDelete($viewer, $type, $resource, $attachment->uploaded_by),
            ])->all(),
        ];
    }
}
