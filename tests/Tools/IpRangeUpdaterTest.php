<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Tools;

use JacyImp\CloudIpDetector\Provider;
use JacyImp\CloudIpDetector\Tools\IpRanges\IpRangeUpdater;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class IpRangeUpdaterTest extends TestCase
{
    #[Test]
    public function itsGeneratedOutputIsDeterministic(): void
    {
        $target = $this->temporaryTarget();
        $updater = new IpRangeUpdater($target);
        $csv = "Type,Address,Providers,RetiredAt\n"
            . 'IPv4,10.0.0.0/24,' . implode(';', $this->providerIdentifiers()) . ",\n";

        $updater->update($csv);
        $first = file_get_contents($target);
        $updater->update($csv);

        self::assertSame($first, file_get_contents($target));
        @unlink($target);
    }

    #[Test]
    public function aFailedUpdateDoesNotReplaceAValidSnapshot(): void
    {
        $target = $this->temporaryTarget();
        file_put_contents($target, 'valid snapshot');
        $updater = new IpRangeUpdater($target);

        try {
            $updater->update('malformed');
            self::fail('Expected update to fail.');
        } catch (RuntimeException) {
            self::assertSame('valid snapshot', file_get_contents($target));
        } finally {
            @unlink($target);
        }
    }

    private function temporaryTarget(): string
    {
        return sys_get_temp_dir() . '/cloud-ip-detector-' . bin2hex(random_bytes(8)) . '.php';
    }

    /** @return list<string> */
    private function providerIdentifiers(): array
    {
        return array_map(
            static function (Provider $provider): string {
                if ($provider === Provider::DigitalOcean) {
                    return 'digitalocean';
                }

                return str_replace('_', '-', $provider->value);
            },
            Provider::cases(),
        );
    }
}
