<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Internal;

final class CidrMatcher
{
    public static function matches(string $ip, string $cidr): bool
    {
        $separatorPosition = strrpos($cidr, '/');

        if ($separatorPosition === false) {
            return false;
        }

        $network = substr($cidr, 0, $separatorPosition);
        $prefixLength = (int) substr($cidr, $separatorPosition + 1);

        $ipBinary = inet_pton($ip);
        $networkBinary = inet_pton($network);

        if ($ipBinary === false || $networkBinary === false) {
            return false;
        }

        if (strlen($ipBinary) !== strlen($networkBinary)) {
            return false;
        }

        $maximumPrefixLength = strlen($ipBinary) * 8;

        if ($prefixLength < 0 || $prefixLength > $maximumPrefixLength) {
            return false;
        }

        $wholeBytes = intdiv($prefixLength, 8);
        $remainingBits = $prefixLength % 8;

        if (
            $wholeBytes > 0
            && substr($ipBinary, 0, $wholeBytes) !== substr($networkBinary, 0, $wholeBytes)
        ) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($ipBinary[$wholeBytes]) & $mask)
            === (ord($networkBinary[$wholeBytes]) & $mask);
    }
}
