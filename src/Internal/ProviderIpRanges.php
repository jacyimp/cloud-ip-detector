<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final class ProviderIpRanges
{
    /**
     * @var array<string, list<string>>
     */
    private static array $ranges = [];

    /**
     * @return list<string>
     */
    public static function for(Provider $provider): array
    {
        return self::$ranges[$provider->value] ??= self::load($provider);
    }

    /**
     * @return list<string>
     */
    private static function load(Provider $provider): array
    {
        $name = str_replace('_', '-', $provider->value);

        /** @var list<string> $ranges */
        $ranges = require sprintf(
            '%s/../../resources/ip-ranges/%s.php',
            __DIR__,
            $name,
        );

        return $ranges;
    }
}
