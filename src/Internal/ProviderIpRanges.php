<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final class ProviderIpRanges
{
    /**
     * @return list<string>
     */
    public static function for(Provider $provider): array
    {
        return match ($provider) {
            Provider::Cloudflare => CloudflareIpRanges::all(),
            Provider::Aws => AwsIpRanges::all(),
            Provider::GoogleCloud => GoogleCloudIpRanges::all(),
            Provider::Azure => AzureIpRanges::all(),
            Provider::Fastly => FastlyIpRanges::all(),
            Provider::DigitalOcean => DigitalOceanIpRanges::all(),
            Provider::OracleCloud => OracleCloudIpRanges::all(),
        };
    }
}
