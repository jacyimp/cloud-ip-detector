<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector;

interface CloudIpDetectorInterface
{
    public function detect(string $ip): ?Provider;

    public function belongsTo(string $ip, Provider $provider): bool;
}
