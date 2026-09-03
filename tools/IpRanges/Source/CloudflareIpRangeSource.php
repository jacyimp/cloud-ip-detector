<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;

final readonly class CloudflareIpRangeSource implements ProviderIpRangeSource
{
    private const IPV4_URL = 'https://www.cloudflare.com/ips-v4';
    private const IPV6_URL = 'https://www.cloudflare.com/ips-v6';
    private const SOURCE = 'Cloudflare published IP ranges';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::Cloudflare;
    }

    public function fetch(): IpRangeSnapshot
    {
        return new IpRangeSnapshot(
            [
                ...$this->downloader->downloadLines(self::IPV4_URL),
                ...$this->downloader->downloadLines(self::IPV6_URL),
            ],
            self::SOURCE,
        );
    }
}
