<?php

declare(strict_types=1);

const AWS_IP_RANGES_URL = 'https://ip-ranges.amazonaws.com/ip-ranges.json';

$json = file_get_contents(AWS_IP_RANGES_URL);

if ($json === false) {
    throw new RuntimeException('Unable to download AWS IP ranges.');
}

$data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

if (
    !is_array($data)
    || !isset($data['prefixes'], $data['ipv6_prefixes'])
    || !is_array($data['prefixes'])
    || !is_array($data['ipv6_prefixes'])
) {
    throw new RuntimeException('Unexpected AWS IP ranges format.');
}

$ranges = [];

foreach ($data['prefixes'] as $prefix) {
    if (
        is_array($prefix)
        && ($prefix['service'] ?? null) === 'AMAZON'
        && isset($prefix['ip_prefix'])
        && is_string($prefix['ip_prefix'])
    ) {
        $ranges[] = $prefix['ip_prefix'];
    }
}

foreach ($data['ipv6_prefixes'] as $prefix) {
    if (
        is_array($prefix)
        && ($prefix['service'] ?? null) === 'AMAZON'
        && isset($prefix['ipv6_prefix'])
        && is_string($prefix['ipv6_prefix'])
    ) {
        $ranges[] = $prefix['ipv6_prefix'];
    }
}

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
    AWS_IP_RANGES_URL,
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/aws.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write AWS IP ranges.');
}

printf("Updated AWS IP ranges: %d ranges.\n", count($ranges));
