<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector\Tools\IpRanges;

use InvalidArgumentException;
use JacyImp\CloudIpDetector\Provider;

final class ProviderIpRangeSources
{
    /** @var array<string, ProviderIpRangeSource> */
    private array $sources = [];

    /**
     * @param iterable<ProviderIpRangeSource> $sources
     */
    public function __construct(iterable $sources)
    {
        foreach ($sources as $source) {
            $provider = $source->provider();

            if (isset($this->sources[$provider->value])) {
                throw new InvalidArgumentException(
                    sprintf('Duplicate IP range source for provider "%s".', $provider->value),
                );
            }

            $this->sources[$provider->value] = $source;
        }
    }

    public function for(Provider $provider): ProviderIpRangeSource
    {
        return $this->sources[$provider->value]
            ?? throw new InvalidArgumentException(
                sprintf('No IP range source configured for provider "%s".', $provider->value),
            );
    }

    /**
     * @return list<ProviderIpRangeSource>
     */
    public function all(): array
    {
        return array_values($this->sources);
    }
}
