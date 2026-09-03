<?php

declare(strict_types=1);

use JacyImp\CloudIpDetector\Tools\IpRanges\HttpDownloader;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeUpdater;

require __DIR__ . '/../vendor/autoload.php';

const SOURCE = 'https://raw.githubusercontent.com/disposable/cloud-ip-ranges/refs/heads/master/csv/all-providers.csv';

$csv = (new HttpDownloader())->download(SOURCE);
$result = (new IpRangeUpdater(__DIR__ . '/../resources/ip-ranges.php'))->update($csv);
$multiProviderCidrs = 0;
$maximumProviders = 0;

foreach ($result->ranges as [, $providers]) {
    $count = count($providers);
    $maximumProviders = max($maximumProviders, $count);

    if ($count > 1) {
        ++$multiProviderCidrs;
    }
}

printf(
    "Updated %d CIDRs for %d providers (%d active rows, %d retired rows ignored, "
    . "%d multi-provider CIDRs, maximum %d providers).\n",
    count($result->ranges),
    count($result->providerIdentifiers),
    $result->activeRows,
    $result->retiredRows,
    $multiProviderCidrs,
    $maximumProviders,
);
