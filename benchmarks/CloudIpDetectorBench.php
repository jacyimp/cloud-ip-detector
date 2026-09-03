<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Benchmarks;

use JacyImp\CloudIpDetector\CloudIpDetector;
use PhpBench\Attributes as Bench;

#[Bench\Iterations(5)]
#[Bench\Warmup(2)]
final class CloudIpDetectorBench
{
    private CloudIpDetector $detector;

    public function __construct()
    {
        $this->detector = new CloudIpDetector();
    }

    #[Bench\Revs(100)]
    public function benchCloudflareHit(): void
    {
        $this->detector->detectOne('104.16.10.20');
    }

    #[Bench\Revs(10)]
    public function benchAwsHit(): void
    {
        $this->detector->detectOne('3.5.140.1');
    }

    #[Bench\Revs(5)]
    public function benchUnknownIp(): void
    {
        $this->detector->detectOne('192.0.2.1');
    }

    #[Bench\Revs(100)]
    public function benchMultiProviderHit(): void
    {
        $this->detector->detectOne('1.178.4.1');
    }
}
