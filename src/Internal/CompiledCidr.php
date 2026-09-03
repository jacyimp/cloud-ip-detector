<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

final readonly class CompiledCidr
{
    private function __construct(
        private string $network,
        private int $packedLength,
        private int $prefixLength,
        private int $wholePrefixBytes,
        private int $partialByteMask,
    ) {
    }

    public static function from(string $cidr): ?self
    {
        $separatorPosition = strrpos($cidr, '/');

        if ($separatorPosition === false) {
            return null;
        }

        $network = inet_pton(substr($cidr, 0, $separatorPosition));

        if ($network === false) {
            return null;
        }

        $prefixLength = (int) substr($cidr, $separatorPosition + 1);
        $packedLength = strlen($network);

        if ($prefixLength < 0 || $prefixLength > $packedLength * 8) {
            return null;
        }

        $wholePrefixBytes = intdiv($prefixLength, 8);
        $remainingPrefixBits = $prefixLength % 8;

        return new self(
            $network,
            $packedLength,
            $prefixLength,
            $wholePrefixBytes,
            (0xFF << (8 - $remainingPrefixBits)) % 256,
        );
    }

    public function packedLength(): int
    {
        return $this->packedLength;
    }

    public function network(): string
    {
        return $this->network;
    }

    public function prefixLength(): int
    {
        return $this->prefixLength;
    }
    public function firstByte(): ?int
    {
        if ($this->wholePrefixBytes === 0) {
            return null;
        }

        return ord($this->network[0]);
    }

    public function matches(string $ip): bool
    {
        if (strlen($ip) !== $this->packedLength) {
            return false;
        }

        if (
            strncmp($ip, $this->network, $this->wholePrefixBytes) !== 0
        ) {
            return false;
        }

        if ($this->partialByteMask === 0) {
            return true;
        }

        return (ord($ip[$this->wholePrefixBytes]) & $this->partialByteMask)
            === (ord($this->network[$this->wholePrefixBytes]) & $this->partialByteMask);
    }
}
