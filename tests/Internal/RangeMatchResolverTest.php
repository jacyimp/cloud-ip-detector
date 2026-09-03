<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Internal;

use JacyImp\CloudIpDetector\Internal\CompiledCidr;
use JacyImp\CloudIpDetector\Internal\CompiledRange;
use JacyImp\CloudIpDetector\Internal\RangeMatchResolver;
use JacyImp\CloudIpDetector\Provider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RangeMatchResolverTest extends TestCase
{
    #[Test]
    public function longestPrefixWinsRegardlessOfInputOrder(): void
    {
        $broad = $this->range('10.0.0.0/8', Provider::Aws);
        $specific = $this->range('10.1.0.0/16', Provider::Stripe);

        self::assertSame(Provider::Stripe, RangeMatchResolver::resolve([$broad, $specific]));
        self::assertSame(Provider::Stripe, RangeMatchResolver::resolve([$specific, $broad]));
    }

    #[Test]
    public function equalPrefixesUseNormalizedIdentifierPriority(): void
    {
        $range = $this->range('10.0.0.0/8', Provider::Okta, Provider::Aws);

        self::assertSame(Provider::Aws, RangeMatchResolver::resolve([$range]));
    }

    #[Test]
    public function aZeroLengthPrefixCanWin(): void
    {
        self::assertSame(
            Provider::Aws,
            RangeMatchResolver::resolve([$this->range('0.0.0.0/0', Provider::Aws)]),
        );
    }

    private function range(
        string $cidr,
        Provider $provider,
        Provider ...$additionalProviders,
    ): CompiledRange {
        $compiled = CompiledCidr::from($cidr);
        self::assertNotNull($compiled);

        $providers = array_values($additionalProviders);
        array_unshift($providers, $provider);

        return new CompiledRange($compiled, $providers);
    }
}
