<?php

declare(strict_types=1);

const FASTLY_IP_RANGES_URL = 'https://api.fastly.com/public-ip-list';

$json = file_get_contents(FASTLY_IP_RANGES_URL);

if ($json === false) {
    throw new RuntimeException('Unable to download Fastly IP ranges.');
}

$data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

if (
    !is_array($data)
    || !isset($data['addresses'], $data['ipv6_addresses'])
    || !is_array($data['addresses'])
    || !is_array($data['ipv6_addresses'])
) {
    throw new RuntimeException('Unexpected Fastly IP ranges format.');
}

$ranges = [];

foreach ($data['addresses'] as $range) {
    if (!is_string($range)) {
        throw new RuntimeException('Unexpected Fastly IPv4 range.');
    }

    $ranges[] = $range;
}

foreach ($data['ipv6_addresses'] as $range) {
    if (!is_string($range)) {
        throw new RuntimeException('Unexpected Fastly IPv6 range.');
    }

    $ranges[] = $range;
}

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
    FASTLY_IP_RANGES_URL,
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/fastly.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write Fastly IP ranges.');
}

printf(
    "Updated Fastly IP ranges: %d ranges.\n",
    count($ranges),
);
