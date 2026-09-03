<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Internal;

use JacyImp\CloudIpDetector\Internal\CompiledRanges;
use JacyImp\CloudIpDetector\Provider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CompiledRangesTest extends TestCase
{
    #[Test]
    public function itSkipsInvalidRangesAndMatchesEveryBucketWidth(): void
    {
        $ranges = CompiledRanges::from([
            ['invalid', [Provider::Aws->value]],
            ['0.0.0.0/0', [Provider::Cloudflare->value]],
            ['10.0.0.0/8', [Provider::Aws->value]],
            ['10.1.0.0/16', [Provider::Stripe->value]],
        ]);
        $ip = inet_pton('10.1.2.3');
        self::assertIsString($ip);

        self::assertSame(
            [Provider::Stripe, Provider::Aws, Provider::Cloudflare],
            array_map(
                static fn ($range): Provider => $range->providers()[0],
                $ranges->matching($ip),
            ),
        );
    }
}
