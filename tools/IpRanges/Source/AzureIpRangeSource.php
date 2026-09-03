<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges\Source;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeSnapshot;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSource;
use RuntimeException;

final readonly class AzureIpRangeSource implements ProviderIpRangeSource
{
    private const DOWNLOAD_PAGE = 'https://www.microsoft.com/en-us/download/confirmation.aspx?id=56519';
    private const DOWNLOAD_URL_PATTERN = '~https://download\.microsoft\.com/[^"\']+/ServiceTags_Public_\d+\.json~';

    public function __construct(
        private HttpDownloader $downloader,
    ) {
    }

    public function provider(): Provider
    {
        return Provider::Azure;
    }

    public function fetch(): IpRangeSnapshot
    {
        $downloadUrl = $this->downloadUrl();
        $data = $this->downloader->downloadJson($downloadUrl);

        if (!isset($data['values']) || !is_array($data['values'])) {
            throw new RuntimeException('Unexpected Azure IP ranges format.');
        }

        foreach ($data['values'] as $serviceTag) {
            if (!is_array($serviceTag) || ($serviceTag['name'] ?? null) !== 'AzureCloud') {
                continue;
            }

            $properties = $serviceTag['properties'] ?? null;

            if (
                !is_array($properties)
                || !isset($properties['addressPrefixes'])
                || !is_array($properties['addressPrefixes'])
            ) {
                throw new RuntimeException('AzureCloud service tag has an unexpected format.');
            }

            $ranges = [];

            foreach ($properties['addressPrefixes'] as $range) {
                if (is_string($range)) {
                    $ranges[] = $range;
                }
            }

            return new IpRangeSnapshot($ranges, $downloadUrl);
        }

        throw new RuntimeException('AzureCloud service tag was not found.');
    }

    private function downloadUrl(): string
    {
        $html = $this->downloader->download(self::DOWNLOAD_PAGE);

        if (preg_match(self::DOWNLOAD_URL_PATTERN, $html, $matches) !== 1) {
            throw new RuntimeException('Unable to find the Azure IP ranges download URL.');
        }

        return html_entity_decode($matches[0]);
    }
}
