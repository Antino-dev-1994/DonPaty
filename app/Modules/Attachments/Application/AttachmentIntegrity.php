<?php

namespace App\Modules\Attachments\Application;

use App\Modules\Attachments\Domain\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AttachmentIntegrity
{
    public function verify(Attachment $attachment): bool
    {
        $stream = Storage::disk($attachment->disk)->readStream($attachment->path);
        if (! is_resource($stream)) {
            return false;
        }
        try {
            $context = hash_init('sha256');
            hash_update_stream($context, $stream);

            return hash_equals($attachment->sha256, hash_final($context));
        } finally {
            fclose($stream);
        }
    }
}
