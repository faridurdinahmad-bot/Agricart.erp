<?php

namespace App\Modules\Catalog\Support;

final class BrandWebsiteFormatter
{
    public const DEFAULT_PROTOCOL = 'https://www.';

    /**
     * @return list<string>
     */
    public static function protocols(): array
    {
        return [
            'https://www.',
            'http://www.',
            'https://',
            'http://',
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function protocolOptions(): array
    {
        return [
            ['value' => 'https://www.', 'label' => 'https://www.'],
            ['value' => 'http://www.', 'label' => 'http://www.'],
            ['value' => 'https://', 'label' => 'https://'],
            ['value' => 'http://', 'label' => 'http://'],
        ];
    }

    /**
     * @return array{protocol: string, domain: string}
     */
    public static function split(?string $url): array
    {
        $url = trim((string) $url);

        if ($url === '') {
            return [
                'protocol' => self::DEFAULT_PROTOCOL,
                'domain' => '',
            ];
        }

        foreach (self::protocols() as $protocol) {
            if (str_starts_with(strtolower($url), strtolower($protocol))) {
                return [
                    'protocol' => $protocol,
                    'domain' => substr($url, strlen($protocol)),
                ];
            }
        }

        return [
            'protocol' => self::DEFAULT_PROTOCOL,
            'domain' => ltrim($url, '/'),
        ];
    }

    public static function merge(?string $protocol, ?string $domain): ?string
    {
        $domain = trim((string) $domain);

        if ($domain === '') {
            return null;
        }

        $parts = self::split($domain);
        $domain = $parts['domain'];
        $protocol = self::normalizeProtocol($protocol ?: $parts['protocol']);

        if ($domain === '') {
            return null;
        }

        return $protocol.$domain;
    }

    public static function normalizeProtocol(?string $protocol): string
    {
        $protocol = (string) $protocol;

        return in_array($protocol, self::protocols(), true)
            ? $protocol
            : self::DEFAULT_PROTOCOL;
    }
}
