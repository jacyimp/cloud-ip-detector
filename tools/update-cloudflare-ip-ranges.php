<?php

declare(strict_types=1);

const CLOUDFLARE_IPV4_RANGES_URL = 'https://www.cloudflare.com/ips-v4';
const CLOUDFLARE_IPV6_RANGES_URL = 'https://www.cloudflare.com/ips-v6';

$ranges = [
    ...downloadRanges(CLOUDFLARE_IPV4_RANGES_URL),
    ...downloadRanges(CLOUDFLARE_IPV6_RANGES_URL),
];

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

if ($ranges === []) {
    throw new RuntimeException('Cloudflare IP ranges are empty.');
}

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from Cloudflare's published IP ranges.\n\nreturn %s;\n",
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/cloudflare.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write Cloudflare IP ranges.');
}

printf(
    "Updated Cloudflare IP ranges: %d ranges.\n",
    count($ranges),
);

/**
 * @return list<string>
 */
function downloadRanges(string $url): array
{
    $content = file_get_contents($url);

    if ($content === false) {
        throw new RuntimeException(
            sprintf('Unable to download IP ranges from "%s".', $url),
        );
    }

    $ranges = preg_split('/\R/', trim($content));

    if ($ranges === false) {
        throw new RuntimeException(
            sprintf('Unable to parse IP ranges from "%s".', $url),
        );
    }

    return array_values(
        array_filter(
            $ranges,
            static fn (string $range): bool => $range !== '',
        ),
    );
}
