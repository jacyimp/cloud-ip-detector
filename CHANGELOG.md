# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Changed

* Migrate range ingestion to the consolidated `disposable/cloud-ip-ranges` CSV.
* Expand the provider enum to every provider identifier represented by active feed rows.
* Preserve multiple provider memberships for shared and overlapping ranges.
* Ignore every row with a populated `RetiredAt` value.
* Replace provider-specific update tooling with one `composer update:ranges` command.
* Replace singular `detect()` with `detectOne()` and add `detectAll()` for all matches.

## [0.1.0] - 2026-09-03

### Added

* Detect the infrastructure provider associated with an IP address.
* Check whether an IP belongs to a specific provider.
* Support Cloudflare, AWS, Google Cloud, Azure, Fastly, DigitalOcean, and Oracle Cloud.
* Support IPv4 and IPv6 ranges where published by the provider.
* Bundle provider IP range snapshots for offline detection.
* Add tooling for refreshing ranges from authoritative provider sources.
* Add IP address validation with `InvalidIpAddressException`.
* Add PHPUnit, PHPStan, and PHP_CodeSniffer configuration.
