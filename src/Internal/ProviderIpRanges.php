<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final class ProviderIpRanges
{
    /** @var list<array{string, non-empty-list<string>}>|null */
    private static ?array $ranges = null;

    private static ?CompiledRanges $compiled = null;

    /** @return list<string> */
    public static function for(Provider $provider): array
    {
        $ranges = [];

        foreach (self::ranges() as [$cidr, $providers]) {
            if (in_array($provider->value, $providers, true)) {
                $ranges[] = $cidr;
            }
        }

        return $ranges;
    }

    /** @return list<CompiledRange> */
    public static function matching(string $ip): array
    {
        self::$compiled ??= CompiledRanges::from(self::ranges());

        return self::$compiled->matching($ip);
    }

    public static function contains(Provider $provider, string $ip): bool
    {
        foreach (self::matching($ip) as $range) {
            if (in_array($provider, $range->providers(), true)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<array{string, non-empty-list<string>}> */
    private static function ranges(): array
    {
        if (self::$ranges !== null) {
            return self::$ranges;
        }

        /** @var list<array{string, non-empty-list<string>}> $ranges */
        $ranges = require __DIR__ . '/../../resources/ip-ranges.php';

        return self::$ranges = $ranges;
    }
}
