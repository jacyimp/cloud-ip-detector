<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final readonly class CompiledRange
{
    /** @param non-empty-list<Provider> $providers */
    public function __construct(
        private CompiledCidr $cidr,
        private array $providers,
    ) {
    }

    public function packedLength(): int
    {
        return $this->cidr->packedLength();
    }

    public function firstByte(): ?int
    {
        return $this->cidr->firstByte();
    }

    public function secondByte(): ?int
    {
        if ($this->cidr->prefixLength() < 16) {
            return null;
        }

        return ord($this->cidr->network()[1]);
    }

    public function prefixLength(): int
    {
        return $this->cidr->prefixLength();
    }

    public function matches(string $ip): bool
    {
        return $this->cidr->matches($ip);
    }

    /** @return non-empty-list<Provider> */
    public function providers(): array
    {
        return $this->providers;
    }
}
