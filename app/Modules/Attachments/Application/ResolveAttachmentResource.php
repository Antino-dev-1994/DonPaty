<?php

namespace App\Modules\Attachments\Application;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveAttachmentResource
{
    public function execute(string $type, string $id): Model
    {
        $class = config("attachments.resources.{$type}");
        if (! is_string($class) || ! is_subclass_of($class, Model::class)) {
            throw new NotFoundHttpException;
        }

        return $class::query()->findOrFail($id);
    }
}
