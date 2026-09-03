<?php

namespace App\Modules\Attachments\Application;

use App\Models\User;
use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Audit\Application\RecordAuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreAttachment
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(Model $resource, UploadedFile $file, User $uploader): Attachment
    {
        $disk = (string) config('attachments.disk', 'local');
        $extension = strtolower($file->guessExtension() ?: $file->extension());
        $path = sprintf('attachments/%s/%s/%s.%s', now()->format('Y'), now()->format('m'), Str::uuid(), $extension);
        $sha256 = hash_file('sha256', $file->getRealPath());
        if ($sha256 === false) {
            throw new RuntimeException('No fue posible calcular la integridad del adjunto.');
        }
        if (! Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path))) {
            throw new RuntimeException('No fue posible almacenar el adjunto.');
        }

        try {
            $attachment = Attachment::create([
                'attachable_type' => $resource->getMorphClass(),
                'attachable_id' => (string) $resource->getKey(),
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'sha256' => $sha256,
                'uploaded_by' => $uploader->id,
            ]);
            $this->audit->execute('attachment.uploaded', $attachment, $uploader, after: $attachment->only(['attachable_type', 'attachable_id', 'original_name', 'mime_type', 'size', 'sha256']));

            return $attachment;
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
    }
}
