<?php

declare(strict_types=1);

const ORACLE_CLOUD_IP_RANGES_URL = 'https://docs.oracle.com/iaas/tools/public_ip_ranges.json';

$json = file_get_contents(ORACLE_CLOUD_IP_RANGES_URL);

if ($json === false) {
    throw new RuntimeException('Unable to download Oracle Cloud IP ranges.');
}

$data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

if (
    !is_array($data)
    || !isset($data['regions'])
    || !is_array($data['regions'])
) {
    throw new RuntimeException('Unexpected Oracle Cloud IP ranges format.');
}

$ranges = [];

foreach ($data['regions'] as $region) {
    if (!is_array($region)) {
        continue;
    }

    collectRanges($region['cidrs'] ?? null, $ranges);
    collectRanges($region['ipv6_cidrs'] ?? null, $ranges);
}

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

if ($ranges === []) {
    throw new RuntimeException('Oracle Cloud IP ranges are empty.');
}

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
    ORACLE_CLOUD_IP_RANGES_URL,
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/oracle-cloud.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write Oracle Cloud IP ranges.');
}

printf(
    "Updated Oracle Cloud IP ranges: %d ranges.\n",
    count($ranges),
);

/**
 * @param mixed        $entries
 * @param list<string> $ranges
 */
function collectRanges(mixed $entries, array &$ranges): void
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
