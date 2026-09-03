<?php

declare(strict_types=1);

const DIGITAL_OCEAN_IP_RANGES_URL = 'https://digitalocean.com/geo/google.csv';

$stream = fopen(DIGITAL_OCEAN_IP_RANGES_URL, 'rb');

if ($stream === false) {
    throw new RuntimeException('Unable to download DigitalOcean IP ranges.');
}

$ranges = [];

while (($row = fgetcsv($stream)) !== false) {
    $cidr = $row[0] ?? null;

    if (!is_string($cidr) || $cidr === '') {
        continue;
    }

    $ranges[] = $cidr;
}

fclose($stream);

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

if ($ranges === []) {
    throw new RuntimeException('DigitalOcean IP ranges are empty.');
}

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
    DIGITAL_OCEAN_IP_RANGES_URL,
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/digital-ocean.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write DigitalOcean IP ranges.');
}

printf(
    "Updated DigitalOcean IP ranges: %d ranges.\n",
    count($ranges),
);
