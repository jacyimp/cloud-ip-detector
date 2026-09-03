<?php

declare(strict_types=1);

const GOOGLE_CLOUD_IP_RANGES_URL = 'https://www.gstatic.com/ipranges/cloud.json';

$json = file_get_contents(GOOGLE_CLOUD_IP_RANGES_URL);

if ($json === false) {
    throw new RuntimeException('Unable to download Google Cloud IP ranges.');
}

$data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

if (
    !is_array($data)
    || !isset($data['prefixes'])
    || !is_array($data['prefixes'])
) {
    throw new RuntimeException('Unexpected Google Cloud IP ranges format.');
}

$ranges = [];

foreach ($data['prefixes'] as $prefix) {
    if (!is_array($prefix)) {
        continue;
    }

    if (isset($prefix['ipv4Prefix']) && is_string($prefix['ipv4Prefix'])) {
        $ranges[] = $prefix['ipv4Prefix'];
    }

    if (isset($prefix['ipv6Prefix']) && is_string($prefix['ipv6Prefix'])) {
        $ranges[] = $prefix['ipv6Prefix'];
    }
}

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
    GOOGLE_CLOUD_IP_RANGES_URL,
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/google-cloud.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write Google Cloud IP ranges.');
}

printf(
    "Updated Google Cloud IP ranges: %d ranges.\n",
    count($ranges),
);
