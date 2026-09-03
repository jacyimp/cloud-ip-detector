<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;
use RuntimeException;

final readonly class AwsIpRangeSource implements ProviderIpRangeSource
{
    private const URL = 'https://ip-ranges.amazonaws.com/ip-ranges.json';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::Aws;
    }

    public function fetch(): IpRangeSnapshot
    {
        $data = $this->downloader->downloadJson(self::URL);

        if (
            !isset($data['prefixes'], $data['ipv6_prefixes'])
            || !is_array($data['prefixes'])
            || !is_array($data['ipv6_prefixes'])
        ) {
            throw new RuntimeException('Unexpected AWS IP ranges format.');
        }

        $ranges = [];

        foreach ($data['prefixes'] as $prefix) {
            if (
                is_array($prefix)
                && ($prefix['service'] ?? null) === 'AMAZON'
                && isset($prefix['ip_prefix'])
                && is_string($prefix['ip_prefix'])
            ) {
                $ranges[] = $prefix['ip_prefix'];
            }
        }

        foreach ($data['ipv6_prefixes'] as $prefix) {
            if (
                is_array($prefix)
                && ($prefix['service'] ?? null) === 'AMAZON'
                && isset($prefix['ipv6_prefix'])
                && is_string($prefix['ipv6_prefix'])
            ) {
                $ranges[] = $prefix['ipv6_prefix'];
            }
        }

        return new IpRangeSnapshot($ranges, self::URL);
    }
}
