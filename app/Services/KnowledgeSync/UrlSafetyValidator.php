<?php

namespace App\Services\KnowledgeSync;

class UrlSafetyValidator
{
    /**
     * @param string[]|null $blockedHosts
     */
    public function __construct(
        private readonly ?array $blockedHosts = null,
    ) {
    }

    /**
     * @return array{allowed:bool,reason:?string}
     */
    public function validate(string $url, ?string $trustedInternalHost = null): array
    {
        $parts = parse_url($url);

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['allowed' => false, 'reason' => 'Only http/https URLs are allowed.'];
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '') {
            return ['allowed' => false, 'reason' => 'URL host is missing.'];
        }

        if ($this->isBlockedHost($host)) {
            return ['allowed' => false, 'reason' => 'Host is blocked.'];
        }

        $allowPrivateForHost = $trustedInternalHost !== null && strtolower($trustedInternalHost) === $host;

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!$allowPrivateForHost && $this->isBlockedIp($host)) {
                return ['allowed' => false, 'reason' => 'IP address is not allowed.'];
            }

            return ['allowed' => true, 'reason' => null];
        }

        $ips = gethostbynamel($host) ?: [];
        if ($ips === []) {
            return ['allowed' => false, 'reason' => 'Host DNS resolution failed.'];
        }

        foreach ($ips as $ip) {
            if (!$allowPrivateForHost && $this->isBlockedIp($ip)) {
                return ['allowed' => false, 'reason' => 'Host resolves to blocked IP range.'];
            }
        }

        return ['allowed' => true, 'reason' => null];
    }

    private function isBlockedHost(string $host): bool
    {
        $blocked = $this->blockedHosts;
        if ($blocked === null) {
            $blocked = $this->defaultBlockedHosts();
        }

        $blocked = array_map('strtolower', $blocked);

        if (in_array($host, $blocked, true)) {
            return true;
        }

        return str_ends_with($host, '.localhost');
    }

    private function isBlockedIp(string $ip): bool
    {
        if ($ip === '169.254.169.254') {
            return true;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->isBlockedIpv6($ip);
        }

        return true;
    }

    private function isBlockedIpv6(string $ip): bool
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return true;
        }

        if ($ip === '::1' || $ip === '::') {
            return true;
        }

        $firstByte = ord($packed[0]);
        $secondByte = ord($packed[1]);

        // fc00::/7 unique local addresses
        if (($firstByte & 0xfe) === 0xfc) {
            return true;
        }

        // fe80::/10 link-local addresses
        if ($firstByte === 0xfe && ($secondByte & 0xc0) === 0x80) {
            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function defaultBlockedHosts(): array
    {
        try {
            if (function_exists('app') && app()->bound('config')) {
                return (array) config('knowledge_sync.fetch.blocked_hosts', []);
            }
        } catch (\Throwable) {
        }

        return ['localhost', '127.0.0.1', '::1', '0.0.0.0', '169.254.169.254'];
    }
}
