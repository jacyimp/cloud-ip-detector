<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

final readonly class IpRangeSnapshot
{
    /**
     * @param list<string> $ranges
     */
    public function __construct(
        public array $ranges,
        public string $source,
    ) {
    }
}
