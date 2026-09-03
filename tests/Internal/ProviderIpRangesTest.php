<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Internal;

use JacyImp\CloudIpDetector\Internal\ProviderIpRanges;
use JacyImp\CloudIpDetector\Provider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ProviderIpRangesTest extends TestCase
{
    #[Test]
    public function enumMatchesTheActiveProviderUniverse(): void
    {
        self::assertCount(76, Provider::cases());
    }

    #[Test]
    #[DataProvider('providers')]
    public function everyProviderHasIpRanges(Provider $provider): void
    {
        self::assertNotEmpty(
            ProviderIpRanges::for($provider),
        );
    }

    #[Test]
    #[DataProvider('providers')]
    public function everyProviderRangeIsAValidCidr(Provider $provider): void
    {
        foreach (ProviderIpRanges::for($provider) as $cidr) {
            self::assertValidCidr($cidr);
        }
    }

    #[Test]
    #[DataProvider('providers')]
    public function providerRangesDoNotContainDuplicates(Provider $provider): void
    {
        $ranges = ProviderIpRanges::for($provider);

        self::assertSame(
            count($ranges),
            count(array_unique($ranges)),
        );
    }

    #[Test]
    public function compiledRangesAreCached(): void
    {
        $compiled = new ReflectionProperty(ProviderIpRanges::class, 'compiled');
        $compiled->setValue(null, null);

        $ip = inet_pton('192.0.2.1');
        self::assertIsString($ip);

        ProviderIpRanges::matching($ip);
        $firstCompilation = $compiled->getValue();
        ProviderIpRanges::matching($ip);

        self::assertSame($firstCompilation, $compiled->getValue());
    }

    /**
     * @return iterable<string, array{Provider}>
     */
    public static function providers(): iterable
    {
        foreach (Provider::cases() as $provider) {
            yield $provider->value => [$provider];
        }
    }

    private static function assertValidCidr(string $cidr): void
    {
        $separatorPosition = strrpos($cidr, '/');

        self::assertNotFalse(
            $separatorPosition,
            sprintf('"%s" is not a CIDR.', $cidr),
        );

        $ip = substr($cidr, 0, $separatorPosition);
        $prefix = substr($cidr, $separatorPosition + 1);

        self::assertNotFalse(
            filter_var($ip, FILTER_VALIDATE_IP),
            sprintf('"%s" contains an invalid IP.', $cidr),
        );

        self::assertMatchesRegularExpression(
            '/^\d+$/',
            $prefix,
            sprintf('"%s" contains an invalid prefix length.', $cidr),
        );

        $maximumPrefixLength = str_contains($ip, ':') ? 128 : 32;

        self::assertLessThanOrEqual(
            $maximumPrefixLength,
            (int) $prefix,
            sprintf('"%s" contains an invalid prefix length.', $cidr),
        );
    }
}
