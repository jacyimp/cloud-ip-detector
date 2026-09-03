<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tests\Tools;

use JacyImp\CloudIpDetector\Tools\IpRanges\ConsolidatedCsvImporter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConsolidatedCsvImporterTest extends TestCase
{
    private ConsolidatedCsvImporter $importer;

    protected function setUp(): void
    {
        $this->importer = new ConsolidatedCsvImporter();
    }

    #[Test]
    public function itImportsOnlyActiveRowsAndPreservesAllMemberships(): void
    {
        $result = $this->importer->import($this->csv(
            "IPv4,10.0.0.1/24,aws;datadog;aws,\n"
            . "IPv4,10.0.0.0/24,okta,\n"
            . "IPv4,192.0.2.0/24,stripe,2025-01-01\n",
        ));

        self::assertSame(2, $result->activeRows);
        self::assertSame(1, $result->retiredRows);
        self::assertSame([
            ['10.0.0.0/24', ['aws', 'datadog', 'okta']],
        ], $result->ranges);
        self::assertSame(['aws', 'datadog', 'okta'], $result->providerIdentifiers);
    }

    #[Test]
    public function itRejectsAnUnknownProvider(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown provider identifier: future-provider');

        $this->importer->import($this->csv("IPv4,10.0.0.0/8,future-provider,\n"));
    }

    #[Test]
    public function itRejectsMalformedRows(): void
    {
        $this->expectExceptionMessage('Malformed CSV row at line 2.');
        $this->importer->import($this->csv("IPv4,10.0.0.0/8,aws\n"));
    }

    #[Test]
    public function itRejectsUnexpectedHeaders(): void
    {
        $this->expectExceptionMessage('Unexpected CSV headers.');
        $this->importer->import("Address,Providers\n10.0.0.0/8,aws\n");
    }

    #[Test]
    public function itRejectsAnEmptyDownload(): void
    {
        $this->expectExceptionMessage('Downloaded CSV is empty.');
        $this->importer->import('');
    }

    #[Test]
    public function itRejectsADataSetWithoutActiveRows(): void
    {
        $this->expectExceptionMessage('Downloaded CSV contains no active ranges.');
        $this->importer->import($this->csv("IPv4,10.0.0.0/8,aws,retired\n"));
    }

    #[Test]
    public function itRejectsInvalidCidrs(): void
    {
        $this->expectExceptionMessage('Invalid CIDR "10.0.0.0/33" at line 2.');
        $this->importer->import($this->csv("IPv4,10.0.0.0/33,aws,\n"));
    }

    private function csv(string $rows): string
    {
        return "Type,Address,Providers,RetiredAt\n" . $rows;
    }
}
