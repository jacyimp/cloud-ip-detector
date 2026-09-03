<?php

declare(strict_types=1);

use RuntimeException;
use SimpleXMLElement;

require dirname(__DIR__) . '/vendor/autoload.php';

$report = $argv[1] ?? null;

if ($report === null || !is_file($report)) {
    throw new RuntimeException('Pass the path to a Clover coverage report.');
}

$coverage = simplexml_load_file($report);

if (!$coverage instanceof SimpleXMLElement) {
    throw new RuntimeException(sprintf('Unable to read coverage report "%s".', $report));
}

$metrics = $coverage->project->metrics;
$statements = (int) $metrics['statements'];
$coveredStatements = (int) $metrics['coveredstatements'];

if ($statements === 0 || $coveredStatements !== $statements) {
    throw new RuntimeException(sprintf(
        'Code coverage is %.2f%% (%d/%d lines); 100%% is required.',
        $statements === 0 ? 0 : ($coveredStatements / $statements) * 100,
        $coveredStatements,
        $statements,
    ));
}

echo sprintf("Code coverage is 100%% (%d/%d lines).\n", $coveredStatements, $statements);
