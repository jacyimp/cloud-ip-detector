<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Internal;

use JacyImp\CloudIpDetector\Internal\CidrMatcher;
use JacyImp\CloudIpDetector\Internal\CompiledCidr;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CidrMatcherTest extends TestCase
{
    #[Test]
    public function itRejectsAnInvalidIpAddress(): void
    {
        self::assertFalse(CidrMatcher::matches('not-an-ip', '10.0.0.0/8'));
    }

    #[Test]
    public function itRejectsMalformedCidrs(): void
    {
        self::assertFalse(CidrMatcher::matches('10.0.0.1', '10.0.0.0'));
        self::assertFalse(CidrMatcher::matches('10.0.0.1', 'not-an-ip/8'));
        self::assertFalse(CidrMatcher::matches('10.0.0.1', '10.0.0.0/-1'));
        self::assertFalse(CidrMatcher::matches('10.0.0.1', '10.0.0.0/33'));
    }

    #[Test]
    public function itRejectsPrefixesBeyondTheAddressWidth(): void
    {
        self::assertNull(CompiledCidr::from('10.0.0.0/33'));
        self::assertNull(CompiledCidr::from('2001:db8::/129'));
    }

    #[Test]
    public function aZeroLengthPrefixStillRejectsTheOtherIpVersion(): void
    {
        $ipv4Range = CompiledCidr::from('0.0.0.0/0');
        self::assertNotNull($ipv4Range);

        $ipv6 = inet_pton('::1');
        self::assertIsString($ipv6);
        self::assertFalse($ipv4Range->matches($ipv6));
    }

    #[Test]
    public function itMatchesAnIpv4AddressInsideARange(): void
    {
        self::assertTrue(
            CidrMatcher::matches('104.16.255.255', '104.16.0.0/13'),
        );
    }

    #[Test]
    public function itRejectsAnIpv4AddressOutsideARange(): void
    {
        self::assertFalse(
            CidrMatcher::matches('104.24.0.0', '104.16.0.0/13'),
        );
    }

    #[Test]
    public function itMatchesTheFirstIpv4AddressInARange(): void
    {
        self::assertTrue(
            CidrMatcher::matches('173.245.48.0', '173.245.48.0/20'),
        );
    }

    #[Test]
    public function itMatchesTheLastIpv4AddressInARange(): void
    {
        self::assertTrue(
            CidrMatcher::matches('173.245.63.255', '173.245.48.0/20'),
        );
    }

    #[Test]
    public function itMatchesAnIpv6AddressInsideARange(): void
    {
        self::assertTrue(
            CidrMatcher::matches('2606:4700:ffff::1', '2606:4700::/32'),
        );
    }

    #[Test]
    public function itRejectsAnIpv6AddressOutsideARange(): void
    {
        self::assertFalse(
            CidrMatcher::matches('2606:4701::1', '2606:4700::/32'),
        );
    }

    #[Test]
    public function itDoesNotMatchAcrossIpVersions(): void
    {
        self::assertFalse(
            CidrMatcher::matches('104.16.0.1', '2606:4700::/32'),
        );

        self::assertFalse(
            CidrMatcher::matches('2606:4700::1', '104.16.0.0/13'),
        );
    }

    #[Test]
    public function itSupportsSingleIpRanges(): void
    {
        self::assertTrue(
            CidrMatcher::matches('192.0.2.10', '192.0.2.10/32'),
        );

        self::assertFalse(
            CidrMatcher::matches('192.0.2.11', '192.0.2.10/32'),
        );
    }

    #[Test]
    public function itSupportsZeroLengthPrefixes(): void
    {
        self::assertTrue(
            CidrMatcher::matches('203.0.113.10', '0.0.0.0/0'),
        );

        self::assertTrue(
            CidrMatcher::matches('2001:db8::1', '::/0'),
        );
    }
}
