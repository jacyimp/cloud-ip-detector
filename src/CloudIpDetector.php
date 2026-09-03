<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector;

use JacyImp\CloudIpDetector\Exception\InvalidIpAddressException;
use JacyImp\CloudIpDetector\Internal\ProviderIpRanges;
use JacyImp\CloudIpDetector\Internal\RangeMatchResolver;

final class CloudIpDetector implements CloudIpDetectorInterface
{
    public function detectOne(string $ip): ?Provider
    {
        $packedIp = $this->packIp($ip);

        return RangeMatchResolver::resolve(ProviderIpRanges::matching($packedIp));
    }

    /** @return list<Provider> */
    public function detectAll(string $ip): array
    {
        $packedIp = $this->packIp($ip);
        $providers = [];

        foreach (ProviderIpRanges::matching($packedIp) as $range) {
            foreach ($range->providers() as $provider) {
                $providers[$provider->value] = $provider;
            }
        }

        ksort($providers, SORT_STRING);

        return array_values($providers);
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
