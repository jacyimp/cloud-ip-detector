<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final class CompiledRanges
{
    /**
     * @param array<int, array<int, array<int, list<CompiledRange>>>> $buckets
     * @param array<int, array<int, list<CompiledRange>>> $firstByteRanges
     * @param array<int, list<CompiledRange>> $broadRanges
     */
    private function __construct(
        private readonly array $buckets,
        private readonly array $firstByteRanges,
        private readonly array $broadRanges,
    ) {
    }

    /** @param list<array{string, non-empty-list<string>}> $ranges */
    public static function from(array $ranges): self
    {
        $buckets = [];
        $firstByteRanges = [];
        $broadRanges = [];

        foreach ($ranges as [$cidr, $providerValues]) {
            $compiledCidr = CompiledCidr::from($cidr);

            if ($compiledCidr === null) {
                continue;
            }

            $providers = array_map(
                static fn (string $value): Provider => Provider::from($value),
                $providerValues,
            );
            $range = new CompiledRange($compiledCidr, $providers);
            $packedLength = $range->packedLength();
            $firstByte = $range->firstByte();
            $secondByte = $range->secondByte();

            if ($firstByte === null) {
                $broadRanges[$packedLength][] = $range;
                continue;
            }

            if ($secondByte === null) {
                $firstByteRanges[$packedLength][$firstByte][] = $range;
                continue;
            }

            $buckets[$packedLength][$firstByte][$secondByte][] = $range;
        }

        return new self($buckets, $firstByteRanges, $broadRanges);
    }

    /** @return list<CompiledRange> */
    public function matching(string $ip): array
    {
        $packedLength = strlen($ip);
        $firstByte = ord($ip[0]);
        $secondByte = ord($ip[1]);
        $matches = [];

        foreach ($this->buckets[$packedLength][$firstByte][$secondByte] ?? [] as $range) {
            if ($range->matches($ip)) {
                $matches[] = $range;
            }
        }

        foreach ($this->firstByteRanges[$packedLength][$firstByte] ?? [] as $range) {
            if ($range->matches($ip)) {
                $matches[] = $range;
            }
        }

        foreach ($this->broadRanges[$packedLength] ?? [] as $range) {
            if ($range->matches($ip)) {
                $matches[] = $range;
            }
        }

        return $matches;
    }
}
