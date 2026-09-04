<?php

namespace Tests\Unit;

use App\Support\Deployment\LanAccessUrl;
use PHPUnit\Framework\TestCase;

class LanAccessUrlTest extends TestCase
{
    public function test_it_accepts_only_http_urls_with_private_ipv4_addresses(): void
    {
        $url = LanAccessUrl::from('http://192.168.1.25:8000');

        $this->assertNotNull($url);
        $this->assertSame('192.168.1.25', $url->host);
        $this->assertSame(8000, $url->port);
        $this->assertNull(LanAccessUrl::from('http://localhost:8000'));
        $this->assertNull(LanAccessUrl::from('https://192.168.1.25'));
        $this->assertNull(LanAccessUrl::from('http://8.8.8.8'));
    }
}
