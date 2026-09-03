<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use JacyImp\CloudIpDetector\Provider;
use RuntimeException;

final class ProviderIdentifier
{
    public static function toProvider(string $identifier): Provider
    {
        $value = str_replace('-', '_', $identifier);

        // The upstream spelling has no separator; retain the established value.
        if ($identifier === 'digitalocean') {
            $value = 'digital_ocean';
        }

        $provider = Provider::tryFrom($value);

        if ($provider === null) {
            throw new RuntimeException(
                sprintf('Unknown provider identifier: %s', $identifier),
            );
        }

        return $provider;
    }
}
