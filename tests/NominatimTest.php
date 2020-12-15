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
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class NominatimTest extends ProviderIntegrationTest
{
    protected $testIpv4 = false;
    protected $testIpv6 = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return Nominatim::withOpenStreetMapServer($httpClient, 'Geocoder PHP/Nominatim Provider/Nominatim Test');
    }

    protected function getCacheDir()
    {
        return dirname(__DIR__).'/.cached_responses';
    }

    protected function getApiKey()
    {
        return '';
    }
}
