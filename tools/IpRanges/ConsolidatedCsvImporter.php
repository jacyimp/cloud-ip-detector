<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use RuntimeException;

final class ConsolidatedCsvImporter
{
    private const HEADERS = ['Type', 'Address', 'Providers', 'RetiredAt'];

    public function import(string $csv): ImportedRanges
    {
        if (trim($csv) === '') {
            throw new RuntimeException('Downloaded CSV is empty.');
        }

        $stream = fopen('php://temp', 'r+');

        if ($stream === false || fwrite($stream, $csv) === false) {
            throw new RuntimeException('Unable to buffer downloaded CSV.');
        }

        rewind($stream);
        $headers = fgetcsv($stream, escape: '');

        if ($headers !== self::HEADERS) {
            fclose($stream);
            throw new RuntimeException(sprintf(
                'Unexpected CSV headers. Expected: %s.',
                implode(',', self::HEADERS),
            ));
        }

        /** @var array<string, array<string, true>> $memberships */
        $memberships = [];
        /** @var array<string, true> $identifiers */
        $identifiers = [];
        $activeRows = 0;
        $retiredRows = 0;
        $line = 1;

        while (($row = fgetcsv($stream, escape: '')) !== false) {
            ++$line;

            if ($row === [null]) {
                continue;
            }

            if (count($row) !== count(self::HEADERS) || in_array(null, $row, true)) {
                fclose($stream);
                throw new RuntimeException(sprintf('Malformed CSV row at line %d.', $line));
            }

            [$type, $address, $providers, $retiredAt] = $row;

            if ($retiredAt !== '') {
                ++$retiredRows;
                continue;
            }

            ++$activeRows;
            $cidr = $this->normalizeCidr($address, $type, $line);
            $providerIdentifiers = explode(';', $providers);

            if ($providerIdentifiers === ['']) {
                fclose($stream);
                throw new RuntimeException(sprintf('Missing provider at line %d.', $line));
            }

            foreach ($providerIdentifiers as $identifier) {
                if ($identifier === '') {
                    fclose($stream);
                    throw new RuntimeException(sprintf('Missing provider at line %d.', $line));
                }

                $provider = ProviderIdentifier::toProvider($identifier);
                $memberships[$cidr][$provider->value] = true;
                $identifiers[$identifier] = true;
            }
        }

        fclose($stream);

        if ($activeRows === 0 || $memberships === []) {
            throw new RuntimeException('Downloaded CSV contains no active ranges.');
        }

        ksort($memberships, SORT_STRING);
        $ranges = [];

        foreach ($memberships as $cidr => $providers) {
            $values = array_keys($providers);
            sort($values, SORT_STRING);

            if ($values === []) {
                throw new RuntimeException(sprintf('CIDR "%s" has no providers.', $cidr));
            }

            $ranges[] = [$cidr, $values];
        }

        $providerIdentifiers = array_keys($identifiers);
        sort($providerIdentifiers, SORT_STRING);

        return new ImportedRanges($ranges, $providerIdentifiers, $activeRows, $retiredRows);
    }

    private function normalizeCidr(string $cidr, string $type, int $line): string
    {
        $separator = strrpos($cidr, '/');

        if ($separator === false) {
            throw new RuntimeException(sprintf('Invalid CIDR "%s" at line %d.', $cidr, $line));
        }

        $ip = substr($cidr, 0, $separator);
        $prefixText = substr($cidr, $separator + 1);
        $packed = inet_pton($ip);

        if ($packed === false || preg_match('/^\d+$/D', $prefixText) !== 1) {
            throw new RuntimeException(sprintf('Invalid CIDR "%s" at line %d.', $cidr, $line));
        }

        $bits = strlen($packed) * 8;
        $prefix = (int) $prefixText;
        $expectedType = $bits === 32 ? 'IPv4' : 'IPv6';

        if ($prefix > $bits || $type !== $expectedType) {
            throw new RuntimeException(sprintf('Invalid CIDR "%s" at line %d.', $cidr, $line));
        }

        for ($index = intdiv($prefix + 7, 8); $index < strlen($packed); ++$index) {
            $packed[$index] = "\0";
        }

        if ($prefix % 8 !== 0) {
            $index = intdiv($prefix, 8);
            $mask = (0xFF << (8 - ($prefix % 8))) & 0xFF;
            $packed[$index] = chr(ord($packed[$index]) & $mask);
        }

        $network = inet_ntop($packed);

        if ($network === false) {
            throw new RuntimeException(sprintf('Invalid CIDR "%s" at line %d.', $cidr, $line));
        }

        return sprintf('%s/%d', $network, $prefix);
    }
}
