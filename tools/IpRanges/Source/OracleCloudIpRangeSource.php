<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;
use RuntimeException;

final readonly class OracleCloudIpRangeSource implements ProviderIpRangeSource
{
    private const URL = 'https://docs.oracle.com/iaas/tools/public_ip_ranges.json';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::OracleCloud;
    }

    public function fetch(): IpRangeSnapshot
    {
        $data = $this->downloader->downloadJson(self::URL);

        if (!isset($data['regions']) || !is_array($data['regions'])) {
            throw new RuntimeException('Unexpected Oracle Cloud IP ranges format.');
        }

        $ranges = [];

        foreach ($data['regions'] as $region) {
            if (!is_array($region)) {
                continue;
            }

            $this->collectRanges($region['cidrs'] ?? null, $ranges);
            $this->collectRanges($region['ipv6_cidrs'] ?? null, $ranges);
        }

        return new IpRangeSnapshot($ranges, self::URL);
    }

    /**
     * @param mixed        $entries
     * @param list<string> $ranges
     */
    private function collectRanges(mixed $entries, array &$ranges): void
    {
        if (!is_array($entries)) {
            return;
        }

        foreach ($entries as $entry) {
            if (
                is_array($entry)
                && isset($entry['cidr'])
                && is_string($entry['cidr'])
            ) {
                $ranges[] = $entry['cidr'];
            }
        }
    }
}
