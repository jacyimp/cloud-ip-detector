<?php

declare(strict_types=1);

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeUpdater;
use JacyImp\CloudIpDetector\Tools\IpRanges\ProviderIpRangeSources;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\AwsIpRangeSource;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\AzureIpRangeSource;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\CloudflareIpRangeSource;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\DigitalOceanIpRangeSource;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\FastlyIpRangeSource;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\GoogleCloudIpRangeSource;
use JacyImp\CloudIpDetector\Tools\IpRanges\Source\OracleCloudIpRangeSource;

require __DIR__ . '/../vendor/autoload.php';

$downloader = new HttpDownloader();
$sources = new ProviderIpRangeSources([
    new CloudflareIpRangeSource($downloader),
    new AwsIpRangeSource($downloader),
    new GoogleCloudIpRangeSource($downloader),
    new AzureIpRangeSource($downloader),
    new FastlyIpRangeSource($downloader),
    new DigitalOceanIpRangeSource($downloader),
    new OracleCloudIpRangeSource($downloader),
]);
$updater = new IpRangeUpdater(__DIR__ . '/../resources/ip-ranges');
$requestedProvider = $argv[1] ?? 'all';

if ($requestedProvider === 'all') {
    foreach ($sources->all() as $source) {
        $updater->update($source);
    }

    exit(0);
}

$provider = Provider::tryFrom(str_replace('-', '_', $requestedProvider));

if ($provider === null) {
    fwrite(STDERR, sprintf("Unknown provider \"%s\".\n", $requestedProvider));
    exit(1);
}

$updater->update($sources->for($provider));
