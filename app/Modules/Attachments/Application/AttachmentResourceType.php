<?php

namespace App\Modules\Attachments\Application;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class AttachmentResourceType
{
    public function execute(Model $resource): string
    {
        foreach ((array) config('attachments.resources') as $type => $class) {
            if ($resource instanceof $class) {
                return $type;
            }
        }

        throw new RuntimeException('El tipo de recurso adjunto no está configurado.');
    }
}
