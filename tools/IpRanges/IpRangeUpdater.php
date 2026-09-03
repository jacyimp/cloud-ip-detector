<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use JacyImp\CloudIpDetector\Provider;
use RuntimeException;
use Throwable;

final readonly class IpRangeUpdater
{
    public function __construct(
        private string $target,
        private ConsolidatedCsvImporter $importer = new ConsolidatedCsvImporter(),
    ) {
    }

    public function update(string $csv): ImportedRanges
    {
        $imported = $this->importer->import($csv);
        $this->validateCoverage($imported);
        $output = $this->render($imported->ranges);
        $this->validateOutput($output);
        $this->writeAtomically($output);

        return $imported;
    }

    private function validateCoverage(ImportedRanges $imported): void
    {
        $actual = array_map(
            static fn (string $identifier): string => ProviderIdentifier::toProvider($identifier)->value,
            $imported->providerIdentifiers,
        );
        $expected = array_map(
            static fn (Provider $provider): string => $provider->value,
            Provider::cases(),
        );
        sort($actual, SORT_STRING);
        sort($expected, SORT_STRING);

        if ($actual !== $expected) {
            throw new RuntimeException(sprintf(
                'Provider coverage mismatch: expected %d providers, found %d.',
                count($expected),
                count($actual),
            ));
        }
    }

    /** @param list<array{string, non-empty-list<string>}> $ranges */
    public function render(array $ranges): string
    {
        return sprintf(
            "<?php\n\ndeclare(strict_types=1);\n\nreturn %s;\n",
            var_export($ranges, true),
        );
    }

    private function validateOutput(string $output): void
    {
        $temporary = tempnam(sys_get_temp_dir(), 'cloud-ip-ranges-');

        if ($temporary === false) {
            throw new RuntimeException('Unable to create snapshot validation file.');
        }

        try {
            if (file_put_contents($temporary, $output) === false) {
                throw new RuntimeException('Unable to write snapshot validation file.');
            }

            $loaded = require $temporary;

            if (!is_array($loaded) || $loaded === []) {
                throw new RuntimeException('Generated runtime dataset is empty or invalid.');
            }
        } finally {
            @unlink($temporary);
        }
    }

    private function writeAtomically(string $output): void
    {
        $directory = dirname($this->target);

        if (!is_dir($directory) && !mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create directory "%s".', $directory));
        }

        $temporary = tempnam($directory, '.ip-ranges-');

        if ($temporary === false) {
            throw new RuntimeException('Unable to create temporary snapshot.');
        }

        try {
            if (file_put_contents($temporary, $output, LOCK_EX) === false) {
                throw new RuntimeException('Unable to write temporary snapshot.');
            }

            if (!rename($temporary, $this->target)) {
                throw new RuntimeException(sprintf('Unable to replace snapshot "%s".', $this->target));
            }
        } catch (Throwable $exception) {
            @unlink($temporary);
            throw $exception;
        }
    }
}
