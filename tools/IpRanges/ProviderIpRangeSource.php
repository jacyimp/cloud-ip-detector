<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use JacyImp\CloudIpDetector\Provider;

interface ProviderIpRangeSource
{
    public function provider(): Provider;

    public function fetch(): IpRangeSnapshot;
}
