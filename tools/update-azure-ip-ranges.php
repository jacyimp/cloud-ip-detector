<?php

declare(strict_types=1);

const AZURE_DOWNLOAD_PAGE = 'https://www.microsoft.com/en-us/download/confirmation.aspx?id=56519';

$html = file_get_contents(AZURE_DOWNLOAD_PAGE);

if ($html === false) {
    throw new RuntimeException('Unable to load the Azure IP ranges download page.');
}

if (
    preg_match(
        '~https://download\.microsoft\.com/[^"\']+/ServiceTags_Public_\d+\.json~',
        $html,
        $matches,
    ) !== 1
) {
    throw new RuntimeException('Unable to find the Azure IP ranges download URL.');
}

$downloadUrl = html_entity_decode($matches[0]);

$json = file_get_contents($downloadUrl);

if ($json === false) {
    throw new RuntimeException('Unable to download Azure IP ranges.');
}

$data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

if (
    !is_array($data)
    || !isset($data['values'])
    || !is_array($data['values'])
) {
    throw new RuntimeException('Unexpected Azure IP ranges format.');
}

$azureCloud = null;

foreach ($data['values'] as $serviceTag) {
    if (
        is_array($serviceTag)
        && ($serviceTag['name'] ?? null) === 'AzureCloud'
    ) {
        $azureCloud = $serviceTag;

        break;
    }
}

if ($azureCloud === null) {
    throw new RuntimeException('AzureCloud service tag was not found.');
}

$properties = $azureCloud['properties'] ?? null;

if (
    !is_array($properties)
    || !isset($properties['addressPrefixes'])
    || !is_array($properties['addressPrefixes'])
) {
    throw new RuntimeException('AzureCloud service tag has an unexpected format.');
}

$ranges = array_values(
    array_filter(
        $properties['addressPrefixes'],
        is_string(...),
    ),
);

$ranges = array_values(array_unique($ranges));

sort($ranges, SORT_STRING);

$output = sprintf(
    "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
    $downloadUrl,
    var_export($ranges, true),
);

$target = __DIR__ . '/../resources/ip-ranges/azure.php';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), recursive: true);
}

if (file_put_contents($target, $output) === false) {
    throw new RuntimeException('Unable to write Azure IP ranges.');
}

printf(
    "Updated Azure IP ranges: %d ranges.\n",
    count($ranges),
);
