<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector;

use JacyImp\CloudIpDetector\Exception\InvalidIpAddressException;
use JacyImp\CloudIpDetector\Internal\CidrMatcher;
use JacyImp\CloudIpDetector\Internal\ProviderIpRanges;

final class CloudIpDetector implements CloudIpDetectorInterface
{
    public function detect(string $ip): ?Provider
    {
        $this->assertValidIp($ip);

        foreach (Provider::cases() as $provider) {
            if ($this->belongsToProvider($ip, $provider)) {
                return $provider;
            }
        }

        return null;
    }

    public function belongsTo(string $ip, Provider $provider): bool
    {
        $this->assertValidIp($ip);

        return $this->belongsToProvider($ip, $provider);
    }

    private function belongsToProvider(string $ip, Provider $provider): bool
    {
        foreach (ProviderIpRanges::for($provider) as $cidr) {
            if (CidrMatcher::matches($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    private function assertValidIp(string $ip): void
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw InvalidIpAddressException::for($ip);
        }
    }
}
