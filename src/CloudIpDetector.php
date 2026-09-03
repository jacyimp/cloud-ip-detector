<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector;

use JacyImp\CloudIpDetector\Exception\InvalidIpAddressException;
use JacyImp\CloudIpDetector\Internal\ProviderIpRanges;

final class CloudIpDetector implements CloudIpDetectorInterface
{
    public function detect(string $ip): ?Provider
    {
        $packedIp = $this->packIp($ip);

        foreach (Provider::cases() as $provider) {
            if (ProviderIpRanges::contains($provider, $packedIp)) {
                return $provider;
            }
        }

        return null;
    }

    public function belongsTo(string $ip, Provider $provider): bool
    {
        $packedIp = $this->packIp($ip);

        return ProviderIpRanges::contains($provider, $packedIp);
    }

    private function packIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw InvalidIpAddressException::for($ip);
        }

        $packedIp = inet_pton($ip);

        if ($packedIp === false) {
            throw InvalidIpAddressException::for($ip);
        }

        return $packedIp;
    }
}
