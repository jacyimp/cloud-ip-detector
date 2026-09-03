# Cloud IP Detector

Identify known provider and service network ownership associated with an IP address.

The package works entirely offline using a bundled snapshot of the consolidated
[`disposable/cloud-ip-ranges`](https://github.com/disposable/cloud-ip-ranges) dataset.

## Installation

```bash
composer require jacyimp/cloud-ip-detector
```

## Usage

```php
use JacyImp\CloudIpDetector\CloudIpDetector;
use JacyImp\CloudIpDetector\Provider;

$detector = new CloudIpDetector();
$provider = $detector->detectOne('104.16.10.20');

$provider === Provider::Cloudflare;
```

Unknown infrastructure returns `null`:

```php
$detector->detectOne('192.0.2.1'); // null
```

Check membership or return every possible provider for an overlapping range:

```php
$detector->belongsTo('1.178.4.1', Provider::Aws);       // true
$detector->belongsTo('1.178.4.1', Provider::HerokuAws); // true
$detector->detectAll('1.178.4.1');

// [Provider::Aws, Provider::HerokuAws]
```

## Dependency Injection

Depend on `CloudIpDetectorInterface` when injecting the detector.

## Supported Providers

All 76 provider identifiers with active rows in `disposable/cloud-ip-ranges` are supported,
including clouds, hosting providers, CDNs, edge and security networks, SaaS products, payment
providers, and crawlers. `Provider::cases()` is the definitive supported-provider list.

A2 Hosting, Adyen, Ahrefs, Akamai, Alibaba, Apple Private Relay, Aruba Cloud, Atlassian, AWS, Backblaze, Bing Bot, Branch, Bunny CDN, Bunny Magic Containers, Choopa, CircleCI, Cisco Webex, Cloudflare, Cyso Cloud, Datadog, DigitalOcean, DreamHost, Equinix Metal, Exoscale, Fastly, Fly.io, Gcore CDN, Gcore Cloud, GitHub, GitLab, GoDaddy, Google Bot, Google Cloud, Grafana Cloud, Gridscale, HCP Terraform, Heroku AWS, Hetzner, Huawei Cloud, Infomaniak, Intercom, IONOS Cloud, Kamatera, Linode, Meta Crawler, Microsoft 365, Microsoft Azure, New Relic Synthetics, nForce, Okta, Online SAS, OpenAI, Open Telekom Cloud, Oracle Cloud, OVH, PagerDuty, Perplexity, Rackspace, Render, Salesforce Hyperforce, Scaleway, Seeweb, Sentry, SoftLayer IBM, Stripe, Telegram, Tencent, UCloud, UpCloud, Vercel, Vultr, Wasabi, Yandex, Yandex Cloud, Zendesk, Zscaler.

## Invalid IP Addresses

Invalid IP addresses passed to any lookup method throw `InvalidIpAddressException`.

## IP Range Data

Provider ranges are bundled with the package. Only upstream rows whose `RetiredAt` value is
empty are imported. The feed aggregates official, ASN/BGP-derived, and third-party sources.
Detection performs no HTTP requests and does not depend on external services at runtime.

A CIDR can belong to several providers. `belongsTo()` can therefore return `true` for multiple
providers for one IP. `detectAll()` returns every provider sorted by normalized identifier.
`detectOne()` returns the provider on the longest-prefix match, then the alphabetically first
normalized provider identifier for a tie. This priority is independent of enum, CSV provider,
and CSV row ordering.

Refresh the bundled snapshot during development with:

```bash
composer update:ranges
```

## Performance

| Lookup | Measured time |
|---|---:|
| Cloudflare hit | 0.002787 ms |
| AWS hit | 0.008180 ms |
| Unknown IP | 0.001207 ms |
| Multi-provider hit | 0.004009 ms |

PHPBench on PHP 8.5.10 with OPcache and Xdebug disabled.

## Development

```bash
composer check
composer bench
```

## License

Cloud IP Detector is open-source software licensed under the MIT license.
