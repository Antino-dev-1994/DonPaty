<?php

namespace Tests\Unit;

use App\Modules\Audit\Application\SanitizeAuditData;
use PHPUnit\Framework\TestCase;

class SanitizeAuditDataTest extends TestCase
{
    public function test_audit_data_is_sanitized_recursively(): void
    {
        $result = (new SanitizeAuditData)->execute([
            'email' => 'owner@example.test',
            'password' => 'unsafe',
            'nested' => ['api_token' => 'unsafe', 'amount' => 5000],
        ]);

        $this->assertSame('owner@example.test', $result['email']);
        $this->assertSame('[REDACTADO]', $result['password']);
        $this->assertSame('[REDACTADO]', $result['nested']['api_token']);
        $this->assertSame(5000, $result['nested']['amount']);
    }
}
