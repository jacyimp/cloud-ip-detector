# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

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
