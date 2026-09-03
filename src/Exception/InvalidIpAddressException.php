<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Exception;

use InvalidArgumentException;

final class InvalidIpAddressException extends InvalidArgumentException
{
    public static function for(string $ip): self
    {
        return new self(sprintf('Invalid IP address "%s".', $ip));
    }
}
