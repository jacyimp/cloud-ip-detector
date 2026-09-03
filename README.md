# Cloud IP Detector

Detect which known cloud or infrastructure provider owns an IP address.

The package works entirely offline using bundled IP range snapshots sourced from the providers themselves.

## Installation

```bash
composer require jacyimp/cloud-ip-detector
```

## Usage

```php
use JacyImp\CloudIpDetector\CloudIpDetector;
use JacyImp\CloudIpDetector\Provider;

$detector = new CloudIpDetector();

$provider = $detector->detect('104.16.10.20');

$provider === Provider::Cloudflare;
```

Unknown infrastructure returns `null`:

```php
$detector->detect('192.0.2.1');

// null
```

Check whether an IP belongs to a specific provider:

```php
$detector->belongsTo(
    '104.16.10.20',
    Provider::Cloudflare,
);

// true
```

## Dependency Injection

Depend on `CloudIpDetectorInterface` when injecting the detector:

```php
use JacyImp\CloudIpDetector\CloudIpDetectorInterface;

final class RequestClassifier
{
    public function __construct(
        private readonly CloudIpDetectorInterface $detector,
    ) {
    }
}
```

## Supported Providers

* Cloudflare
* AWS
* Google Cloud
* Azure
* Fastly
* DigitalOcean
* Oracle Cloud

## Invalid IP Addresses

Invalid IP addresses throw `InvalidIpAddressException`:

```php
use JacyImp\CloudIpDetector\Exception\InvalidIpAddressException;

try {
    $detector->detect('not-an-ip');
} catch (InvalidIpAddressException) {
    // ...
}
```

## IP Range Data

Provider ranges are bundled with the package.

Detection performs no HTTP requests and does not depend on external services at runtime.

The bundled snapshots can be refreshed during development:

```bash
composer update:ranges
```

Individual providers can also be updated:

```bash
composer update:cloudflare
composer update:aws
composer update:google-cloud
composer update:azure
composer update:fastly
composer update:digital-ocean
composer update:oracle-cloud
```

## Performance

| Lookup | Measured time |
|---|---:|
| Cloudflare hit | 0.642 μs |
| AWS hit | 37.253 μs |
| Unknown IP | 17.369 μs |

PHPBench on PHP 8.4.22 with OPcache and Xdebug disabled.

## Development

Run all checks:

```bash
composer check
```

Or run them individually:

```bash
composer phpcs
composer phpstan
composer test
```

## License

Cloud IP Detector is open-source software licensed under the MIT license.
