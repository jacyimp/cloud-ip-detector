<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

final class CompiledProviderRanges
{
    /**
     * @param array<int, array<int, list<CompiledCidr>>> $buckets
     * @param array<int, list<CompiledCidr>> $broadRanges
     */
    private function __construct(
        private readonly array $buckets,
        private readonly array $broadRanges,
    ) {
    }

    /**
     * @param list<string> $ranges
     */
    public static function from(array $ranges): self
    {
        $buckets = [];
        $broadRanges = [];

        foreach ($ranges as $range) {
            $compiledRange = CompiledCidr::from($range);

            if ($compiledRange === null) {
                continue;
            }

            $packedLength = $compiledRange->packedLength();
            $firstByte = $compiledRange->firstByte();

            if ($firstByte === null) {
                $broadRanges[$packedLength][] = $compiledRange;
                continue;
            }

            $buckets[$packedLength][$firstByte][] = $compiledRange;
        }

        return new self($buckets, $broadRanges);
    }

    public function contains(string $ip): bool
    {
        $packedLength = strlen($ip);
        $firstByte = ord($ip[0]);

        foreach ($this->buckets[$packedLength][$firstByte] ?? [] as $range) {
            if ($range->matches($ip)) {
                return true;
            }
        }

        foreach ($this->broadRanges[$packedLength] ?? [] as $range) {
            if ($range->matches($ip)) {
                return true;
            }
        }

        return false;
    }
}
