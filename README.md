# Cloud IP Detector

[![Coverage: 100%](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)](https://github.com/jacyimp/cloud-ip-detector/actions/workflows/ci.yml)
[![PHPStan: max level](https://img.shields.io/badge/PHPStan-max%20level-brightgreen.svg)](https://github.com/jacyimp/cloud-ip-detector/actions/workflows/ci.yml)

Identify known provider and service networks behind an IP address.

Detection is fully offline using bundled IP range data.

## Installation

```bash
composer require jacyimp/cloud-ip-detector
```

## Usage

```php
use JacyImp\CloudIpDetector\CloudIpDetector;
use JacyImp\CloudIpDetector\Provider;

$detector = new CloudIpDetector();

$detector->detectOne('104.16.10.20');
// Provider::Cloudflare
```

Unknown IPs return `null`:

```php
$detector->detectOne('192.0.2.1');
// null
```

### Multiple providers

Some IP ranges belong to more than one provider or service.

```php
$detector->detectAll('1.178.4.1');

// [
//     Provider::Aws,
//     Provider::HerokuAws,
// ]
```

Check membership directly:

```php
$detector->belongsTo('1.178.4.1', Provider::Aws);
// true
```

`detectOne()` returns the most specific match. Ties are resolved deterministically by provider identifier.

## Dependency Injection

Depend on `CloudIpDetectorInterface` when injecting the detector.

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

## Providers

All active providers from [`disposable/cloud-ip-ranges`](https://github.com/disposable/cloud-ip-ranges) are supported.

Current provider coverage includes:

A2 Hosting, Adyen, Ahrefs, Akamai, Alibaba, Apple Private Relay, Aruba Cloud, Atlassian, AWS, Backblaze, Bing Bot, Branch, Bunny CDN, Bunny Magic Containers, Choopa, CircleCI, Cisco Webex, Cloudflare, Cyso Cloud, Datadog, DigitalOcean, DreamHost, Equinix Metal, Exoscale, Fastly, Fly.io, Gcore CDN, Gcore Cloud, GitHub, GitLab, GoDaddy, Google Bot, Google Cloud, Grafana Cloud, Gridscale, HCP Terraform, Heroku AWS, Hetzner, Huawei Cloud, Infomaniak, Intercom, IONOS Cloud, Kamatera, Linode, Meta Crawler, Microsoft 365, Microsoft Azure, New Relic Synthetics, nForce, Okta, Online SAS, OpenAI, Open Telekom Cloud, Oracle Cloud, OVH, PagerDuty, Perplexity, Rackspace, Render, Salesforce Hyperforce, Scaleway, Seeweb, Sentry, SoftLayer IBM, Stripe, Telegram, Tencent, UCloud, UpCloud, Vercel, Vultr, Wasabi, Yandex, Yandex Cloud, Zendesk, and Zscaler.

`Provider::cases()` is the definitive supported-provider list.

## IP Range Data

Ranges are sourced from:

```text
https://raw.githubusercontent.com/disposable/cloud-ip-ranges/refs/heads/master/csv/all-providers.csv
```

Only rows with an empty `RetiredAt` value are included.

The upstream dataset combines official provider ranges with ASN/BGP-derived and third-party sources.

No network requests are performed during detection.

Refresh the bundled snapshot with:

```bash
composer update:ranges
```

## Invalid IP Addresses

Invalid IP addresses passed to any lookup method throw `InvalidIpAddressException`.

## Performance

PHPBench on PHP 8.5.10 with OPcache and Xdebug disabled.

| Lookup             |        Time |
| ------------------ | ----------: |
| Cloudflare hit     | 0.002787 ms |
| AWS hit            | 0.008180 ms |
| Unknown IP         | 0.001207 ms |
| Multi-provider hit | 0.004009 ms |

## Development

```bash
composer check
composer bench
```

## License

MIT
