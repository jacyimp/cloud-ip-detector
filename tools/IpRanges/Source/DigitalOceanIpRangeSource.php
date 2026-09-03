<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;

final readonly class DigitalOceanIpRangeSource implements ProviderIpRangeSource
{
    private const URL = 'https://digitalocean.com/geo/google.csv';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::DigitalOcean;
    }

    public function fetch(): IpRangeSnapshot
    {
        $ranges = [];

        foreach ($this->downloader->downloadLines(self::URL) as $line) {
            $row = str_getcsv($line, ',', '"', '');
            $cidr = $row[0] ?? null;

            if (!is_string($cidr) || $cidr === '') {
                continue;
            }

            $ranges[] = $cidr;
        }

        return new IpRangeSnapshot($ranges, self::URL);
    }
}
