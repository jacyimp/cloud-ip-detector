<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

final readonly class ImportedRanges
{
    /**
     * @param list<array{string, non-empty-list<string>}> $ranges
     * @param list<string> $providerIdentifiers
     */
    public function __construct(
        public array $ranges,
        public array $providerIdentifiers,
        public int $activeRows,
        public int $retiredRows,
    ) {
    }
}
