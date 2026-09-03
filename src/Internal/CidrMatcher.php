<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

final class CidrMatcher
{
    public static function matches(string $ip, string $cidr): bool
    {
        $ipBinary = inet_pton($ip);
        $compiledCidr = CompiledCidr::from($cidr);

        if ($ipBinary === false || $compiledCidr === null) {
            return false;
        }

        return $compiledCidr->matches($ipBinary);
    }
}
