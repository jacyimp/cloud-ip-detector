<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;
use RuntimeException;

final readonly class FastlyIpRangeSource implements ProviderIpRangeSource
{
    private const URL = 'https://api.fastly.com/public-ip-list';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::Fastly;
    }

    public function fetch(): IpRangeSnapshot
    {
        $data = $this->downloader->downloadJson(self::URL);

        if (
            !isset($data['addresses'], $data['ipv6_addresses'])
            || !is_array($data['addresses'])
            || !is_array($data['ipv6_addresses'])
        ) {
            throw new RuntimeException('Unexpected Fastly IP ranges format.');
        }

        $ranges = [];

        foreach ([...$data['addresses'], ...$data['ipv6_addresses']] as $range) {
            if (!is_string($range)) {
                throw new RuntimeException('Unexpected Fastly IP range.');
            }

            $ranges[] = $range;
        }

        return new IpRangeSnapshot($ranges, self::URL);
    }
}
