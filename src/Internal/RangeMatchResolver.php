<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final class RangeMatchResolver
{
    /** @param list<CompiledRange> $ranges */
    public static function resolve(array $ranges): ?Provider
    {
        $bestProvider = null;
        $bestPrefixLength = -1;

        foreach ($ranges as $range) {
            foreach ($range->providers() as $provider) {
                if (
                    $range->prefixLength() > $bestPrefixLength
                    || (
                        $range->prefixLength() === $bestPrefixLength
                        && $bestProvider !== null
                        && ProviderPriority::compare($provider, $bestProvider) < 0
                    )
                ) {
                    $bestProvider = $provider;
                    $bestPrefixLength = $range->prefixLength();
                }
            }
        }

        return $bestProvider;
    }
}
