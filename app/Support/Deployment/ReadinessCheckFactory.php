<?php

namespace App\Support\Deployment;

final class ReadinessCheckFactory
{
    /** @return array{level:string,check:string,message:string} */
    public function make(
        string $check,
        bool $passes,
        string $message,
        string $failureLevel = 'error',
    ): array {
        return [
            'level' => $passes ? 'ok' : $failureLevel,
            'check' => $check,
            'message' => $message,
        ];
    }
}
