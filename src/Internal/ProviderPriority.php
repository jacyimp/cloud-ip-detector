<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

use JacyImp\CloudIpDetector\Provider;

final class ProviderPriority
{
    public static function compare(Provider $left, Provider $right): int
    {
        // Explicit stable priority: ascending normalized provider identifier.
        return $left->value <=> $right->value;
    }
}
