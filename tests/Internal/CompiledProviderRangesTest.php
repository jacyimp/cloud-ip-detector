<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Internal;

use JacyImp\CloudIpDetector\Internal\CompiledProviderRanges;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CompiledProviderRangesTest extends TestCase
{
    #[Test]
    public function itBucketsIpv4AndIpv6RangesByTheirFirstByte(): void
    {
        $ranges = CompiledProviderRanges::from([
            '104.16.0.0/13',
            '2606:4700::/32',
        ]);

        self::assertTrue($ranges->contains((string) inet_pton('104.16.10.20')));
        self::assertTrue($ranges->contains((string) inet_pton('2606:4700::1')));
        self::assertFalse($ranges->contains((string) inet_pton('105.16.10.20')));
        self::assertFalse($ranges->contains((string) inet_pton('2607:4700::1')));
    }

    #[Test]
    public function itKeepsRangesShorterThanEightBitsInFamilyFallbackBuckets(): void
    {
        $ranges = CompiledProviderRanges::from([
            '10.0.0.0/7',
            '2000::/4',
        ]);

        self::assertTrue($ranges->contains((string) inet_pton('11.255.255.255')));
        self::assertFalse($ranges->contains((string) inet_pton('12.0.0.0')));
        self::assertTrue($ranges->contains((string) inet_pton('2fff:ffff::1')));
        self::assertFalse($ranges->contains((string) inet_pton('3000::1')));
    }
}
