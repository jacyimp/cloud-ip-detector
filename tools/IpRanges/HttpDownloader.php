<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use JsonException;
use RuntimeException;

final class HttpDownloader
{
    public function download(string $url): string
    {
        $content = file_get_contents($url);

        if ($content === false) {
            throw new RuntimeException(
                sprintf('Unable to download "%s".', $url),
            );
        }

        return $content;
    }

    /**
     * @return array<mixed>
     *
     * @throws JsonException
     */
    public function downloadJson(string $url): array
    {
        $data = json_decode(
            $this->download($url),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (!is_array($data)) {
            throw new RuntimeException(
                sprintf('Expected JSON object or array from "%s".', $url),
            );
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    public function downloadLines(string $url): array
    {
        $lines = preg_split('/\R/', trim($this->download($url)));

        if ($lines === false) {
            throw new RuntimeException(
                sprintf('Unable to parse lines from "%s".', $url),
            );
        }

        return array_values(
            array_filter(
                $lines,
                static fn (string $line): bool => $line !== '',
            ),
        );
    }
}
