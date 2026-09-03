<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests;

use JacyImp\CloudIpDetector\CloudIpDetector;
use JacyImp\CloudIpDetector\Exception\InvalidIpAddressException;
use JacyImp\CloudIpDetector\Provider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CloudIpDetectorTest extends TestCase
{
    private CloudIpDetector $detector;

    protected function setUp(): void
    {
        $this->detector = new CloudIpDetector();
    }

    #[Test]
    public function itDetectsCloudflareIpv4Infrastructure(): void
    {
        self::assertSame(
            Provider::Cloudflare,
            $this->detector->detect('104.16.10.20'),
        );
    }

    #[Test]
    public function itDetectsCloudflareIpv6Infrastructure(): void
    {
        self::assertSame(
            Provider::Cloudflare,
            $this->detector->detect('2606:4700::1234'),
        );
    }

    #[Test]
    public function itReturnsNullForUnknownInfrastructure(): void
    {
        self::assertNull(
            $this->detector->detect('192.0.2.1'),
        );
    }

    #[Test]
    public function itDeterminesWhetherAnIpBelongsToAProvider(): void
    {
        self::assertTrue(
            $this->detector->belongsTo(
                '172.64.10.20',
                Provider::Cloudflare,
            ),
        );

        self::assertFalse(
            $this->detector->belongsTo(
                '8.8.8.8',
                Provider::Cloudflare,
            ),
        );
    }

    #[Test]
    public function itDetectsGoogleCloudIpv4Infrastructure(): void
    {
        self::assertSame(
            Provider::GoogleCloud,
            $this->detector->detect('34.80.0.1'),
        );
    }

    #[Test]
    public function itDetectsGoogleCloudIpv6Infrastructure(): void
    {
        self::assertSame(
            Provider::GoogleCloud,
            $this->detector->detect('2600:1900:8000::1'),
        );
    }

    #[Test]
    public function itDetectsAzureInfrastructure(): void
    {
        self::assertSame(
            Provider::Azure,
            $this->detector->detect('102.133.0.0'),
        );
    }

    #[Test]
    public function itDetectsFastlyIpv4Infrastructure(): void
    {
        self::assertSame(
            Provider::Fastly,
            $this->detector->detect('151.101.1.1'),
        );
    }

    #[Test]
    public function itDetectsFastlyIpv6Infrastructure(): void
    {
        self::assertSame(
            Provider::Fastly,
            $this->detector->detect('2a04:4e40::1'),
        );
    }

    #[Test]
    public function itDetectsDigitalOceanIpv4Infrastructure(): void
    {
        self::assertSame(
            Provider::DigitalOcean,
            $this->detector->detect('104.131.0.1'),
        );
    }

    #[Test]
    public function itDetectsDigitalOceanIpv6Infrastructure(): void
    {
        self::assertSame(
            Provider::DigitalOcean,
            $this->detector->detect('2604:a880:800:10::17d:2001'),
        );
    }

    #[Test]
    #[DataProvider('knownProviderIps')]
    public function itRecognizesKnownProviderInfrastructure(
        string $ip,
        Provider $provider,
    ): void {
        self::assertSame(
            $provider,
            $this->detector->detect($ip),
        );

        self::assertTrue(
            $this->detector->belongsTo($ip, $provider),
        );
    }

    /**
     * @return iterable<string, array{string, Provider}>
     */
    public static function knownProviderIps(): iterable
    {
        yield 'cloudflare' => [
            '104.16.10.20',
            Provider::Cloudflare,
        ];

        yield 'aws' => [
            '3.5.140.1',
            Provider::Aws,
        ];

        yield 'google-cloud' => [
            '34.80.0.1',
            Provider::GoogleCloud,
        ];

        yield 'azure' => [
            '102.133.0.0',
            Provider::Azure,
        ];

        yield 'fastly' => [
            '151.101.1.1',
            Provider::Fastly,
        ];

        yield 'digital-ocean' => [
            '104.131.0.1',
            Provider::DigitalOcean,
        ];
    }
    #[Test]
    public function itRejectsAnInvalidIpAddress(): void
    {
        $this->expectException(InvalidIpAddressException::class);
        $this->expectExceptionMessage('Invalid IP address "not-an-ip".');

        $this->detector->detect('not-an-ip');
    }

    #[Test]
    public function itDetectsAwsIpv4Infrastructure(): void
    {
        self::assertSame(
            Provider::Aws,
            $this->detector->detect('3.5.140.1'),
        );
    }

    #[Test]
    public function itDetectsOracleCloudIpv4Infrastructure(): void
    {
        self::assertSame(
            Provider::OracleCloud,
            $this->detector->detect('40.233.0.1'),
        );
    }

    #[Test]
    public function itDetectsOracleCloudIpv6Infrastructure(): void
    {
        self::assertSame(
            Provider::OracleCloud,
            $this->detector->detect('2603:c028:8000::1'),
        );
    }

    #[Test]
    public function itDetectsAwsIpv6Infrastructure(): void
    {
        self::assertSame(
            Provider::Aws,
            $this->detector->detect('2a05:d07c:2000::1'),
        );
    }
}
