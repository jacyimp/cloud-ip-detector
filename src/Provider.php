<?php

declare(strict_types=1);

namespace JacyImp\CloudIpDetector;

enum Provider: string
{
    case Cloudflare = 'cloudflare';
    case Aws = 'aws';
    case GoogleCloud = 'google_cloud';
    case Azure = 'azure';
    case Fastly = 'fastly';
    case DigitalOcean = 'digital_ocean';
    case OracleCloud = 'oracle_cloud';
}
