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
        $bestPrefixLength = null;

        foreach ($ranges as $range) {
            foreach ($range->providers() as $provider) {
                if (
                    $bestPrefixLength === null
                    || $range->prefixLength() > $bestPrefixLength
                    || (
                        $range->prefixLength() === $bestPrefixLength
                        && $bestProvider !== null
                        && ProviderPriority::compare($provider, $bestProvider) === -1
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
