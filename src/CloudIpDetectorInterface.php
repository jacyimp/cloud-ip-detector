<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector;

interface CloudIpDetectorInterface
{
    public function detectOne(string $ip): ?Provider;

    /** @return list<Provider> */
    public function detectAll(string $ip): array;

    public function belongsTo(string $ip, Provider $provider): bool;
}
