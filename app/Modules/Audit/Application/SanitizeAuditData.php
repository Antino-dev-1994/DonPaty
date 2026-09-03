<?php

namespace App\Modules\Audit\Application;

use Illuminate\Support\Str;

class SanitizeAuditData
{
    /** @var list<string> */
    private array $sensitiveFragments = [
        'password', 'secret', 'token', 'credential', 'authorization', 'cookie',
        'recovery_code', 'api_key', 'private_key',
    ];

    public function execute(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        return $this->sanitize($data);
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($this->isSensitive((string) $key)) {
                $data[$key] = '[REDACTADO]';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }

    private function isSensitive(string $key): bool
    {
        $normalized = Str::lower($key);

        return collect($this->sensitiveFragments)->contains(
            fn (string $fragment): bool => Str::contains($normalized, $fragment),
        );
    }
}
