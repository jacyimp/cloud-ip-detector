<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use RuntimeException;

final readonly class IpRangeUpdater
{
    public function __construct(
        private string $targetDirectory,
    ) {
    }

    public function update(ProviderIpRangeSource $source): int
    {
        $snapshot = $source->fetch();
        $ranges = $this->normalize($snapshot->ranges);

        if ($ranges === []) {
            throw new RuntimeException(
                sprintf('%s IP ranges are empty.', $source->provider()->name),
            );
        }

        $target = sprintf(
            '%s/%s.php',
            rtrim($this->targetDirectory, '/\\'),
            str_replace('_', '-', $source->provider()->value),
        );

        $this->write($target, $snapshot->source, $ranges);

        printf(
            "Updated %s IP ranges: %d ranges.\n",
            $source->provider()->name,
            count($ranges),
        );

        return count($ranges);
    }

    /**
     * @param list<string> $ranges
     *
     * @return list<string>
     */
    private function normalize(array $ranges): array
    {
        $ranges = array_values(array_unique($ranges));

        sort($ranges, SORT_STRING);

        return $ranges;
    }

    /**
     * @param list<string> $ranges
     */
    private function write(string $target, string $source, array $ranges): void
    {
        $directory = dirname($target);

        if (!is_dir($directory) && !mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new RuntimeException(
                sprintf('Unable to create IP range directory "%s".', $directory),
            );
        }

        $output = sprintf(
            "<?php\n\ndeclare(strict_types=1);\n\n// Generated from %s\n\nreturn %s;\n",
            $source,
            var_export($ranges, true),
        );

        if (file_put_contents($target, $output) === false) {
            throw new RuntimeException(
                sprintf('Unable to write IP ranges to "%s".', $target),
            );
        }
    }
}
