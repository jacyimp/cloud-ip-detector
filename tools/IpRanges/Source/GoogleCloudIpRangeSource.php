<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;
use RuntimeException;

final readonly class GoogleCloudIpRangeSource implements ProviderIpRangeSource
{
    private const URL = 'https://www.gstatic.com/ipranges/cloud.json';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::GoogleCloud;
    }

    public function fetch(): IpRangeSnapshot
    {
        $data = $this->downloader->downloadJson(self::URL);

        if (!isset($data['prefixes']) || !is_array($data['prefixes'])) {
            throw new RuntimeException('Unexpected Google Cloud IP ranges format.');
        }

        $ranges = [];

        foreach ($data['prefixes'] as $prefix) {
            if (!is_array($prefix)) {
                continue;
            }

            if (isset($prefix['ipv4Prefix']) && is_string($prefix['ipv4Prefix'])) {
                $ranges[] = $prefix['ipv4Prefix'];
            }

            if (isset($prefix['ipv6Prefix']) && is_string($prefix['ipv6Prefix'])) {
                $ranges[] = $prefix['ipv6Prefix'];
            }
        }

        return new IpRangeSnapshot($ranges, self::URL);
    }
}
