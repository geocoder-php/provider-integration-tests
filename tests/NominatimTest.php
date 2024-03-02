<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\IntegrationTest\Test;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Nominatim\Nominatim;
use Psr\Http\Client\ClientInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class NominatimTest extends ProviderIntegrationTest
{
    protected static bool $testIpv4 = false;
    protected static bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient)
    {
        return Nominatim::withOpenStreetMapServer($httpClient, 'Geocoder PHP/Nominatim Provider/Nominatim Test');
    }

    protected function getCacheDir(): string
    {
        return dirname(__DIR__).'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return '';
    }
}
