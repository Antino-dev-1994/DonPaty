<?php

namespace App\Support\Deployment;

final readonly class LanAccessUrl
{
    private function __construct(
        public string $value,
        public string $host,
        public int $port,
    ) {}

    public static function from(string $url): ?self
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if ($scheme !== 'http' || ! is_string($host) || ! self::isPrivateIpv4($host)) {
            return null;
        }

        $port = parse_url($url, PHP_URL_PORT) ?: 80;

        return new self(rtrim($url, '/'), $host, (int) $port);
    }

    private static function isPrivateIpv4(string $host): bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        $ip = ip2long($host);

        return $ip !== false && (
            self::between($ip, '10.0.0.0', '10.255.255.255')
            || self::between($ip, '172.16.0.0', '172.31.255.255')
            || self::between($ip, '192.168.0.0', '192.168.255.255')
        );
    }

    private static function between(int $ip, string $start, string $end): bool
    {
        return $ip >= (int) ip2long($start) && $ip <= (int) ip2long($end);
    }
}
