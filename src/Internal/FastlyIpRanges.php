<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

final class FastlyIpRanges
{
    /**
     * @var list<string>|null
     */
    private static ?array $ranges = null;

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        if (self::$ranges !== null) {
            return self::$ranges;
        }

        /** @var list<string> $ranges */
        $ranges = require __DIR__ . '/../../resources/ip-ranges/fastly.php';

        return self::$ranges = $ranges;
    }
}
