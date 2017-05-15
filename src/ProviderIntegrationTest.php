<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\IntegrationTest;

use Geocoder\Collection;
use Geocoder\Location;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class ProviderIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array with functionName => reason
     */
    protected $skippedTests = [];

    /**
     * @return Provider that is used in the tests.
     */
    abstract protected function createProvider(HttpClient $httpClient);

    /**
     * @return string the directory where cached responses are stored
     */
    abstract protected function getCacheDir();

    /**
     * @return string the API key or substring to be removed from cache.
     */
    abstract protected function getApiKey();

    /**
     * @param ResponseInterface $response
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClient
     */
    private function getHttpClient(ResponseInterface $response)
    {
        $client = $this->getMockForAbstractClass(HttpClient::class);

        $client
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn($response);

        return $client;
    }

    /**
     * This client will make real request if cache was not found.
     *
     * @return CachedResponseClient
     */
    private function getCachedHttpClient()
    {
        $client = HttpClientDiscovery::find();

        return new CachedResponseClient($client, $this->getCacheDir(), $this->getApiKey());
    }

    public function testGeocodeQuery()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getCachedHttpClient());
        $query = GeocodeQuery::create('10 Downing St, London, UK')->withLocale('en');
        $result = $provider->geocodeQuery($query);
        $this->assertWellFormattedResult($result);

        // Check Downing Street
        $location = $result->first();
        $this->assertEquals(51.5033, $location->getCoordinates()->getLatitude(), 'Latitude should be in London', 0.1);
        $this->assertEquals(-0.1276, $location->getCoordinates()->getLongitude(), 'Longitude should be in London', 0.1);
        $this->assertContains('Downing', $location->getStreetName(), 'Street name should contain "Downing St"');
        $this->assertContains('10', $location->getStreetNumber(), 'Street number should contain "10"');
    }

    public function testGeocodeQueryWithNoResults()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getCachedHttpClient());
        $query = GeocodeQuery::create('jsajhgsdkfjhsfkjhaldkadjaslgldasd')->withLocale('en');
        $result = $provider->geocodeQuery($query);
        $this->assertWellFormattedResult($result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseQuery()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getCachedHttpClient());

        // Close to the white house
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(38.900206, -77.036991)->withLocale('en'));
        $this->assertWellFormattedResult($result);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     */
    public function testEmptyQuery()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getCachedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create(''));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     */
    public function testNullQuery()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getCachedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create(null));
    }

    public function testEmptyReverseQuery()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getCachedHttpClient());

        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
        $this->assertEquals(0, $result->count());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServer500Error()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(500)));
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServer500ErrorReverse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(500)));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServer400Error()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(400)));
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServer400ErrorReverse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(400)));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServerEmptyResponse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(200)));
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServerEmptyResponseReverse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(200)));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testInvalidCredentialsResponse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(401)));
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testInvalidCredentialsResponseReverse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(401)));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     */
    public function testQuotaExceededResponse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(429)));
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     */
    public function testQuotaExceededResponseReverse()
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $provider = $this->createProvider($this->getHttpClient(new Response(429)));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }

    /**
     * Make sure that a result for a Geocoder is well formatted. Be aware that even
     * a Location with no data may be well formatted.
     *
     * @param $result
     */
    private function assertWellFormattedResult(Collection $result)
    {
        $this->assertInstanceOf(
            Collection::class,
            $result,
            'The result must be an instance of a Geocoder\Collection'
        );

        $this->assertNotEmpty($result, 'Geocoder\Exception should never be empty. A NoResult exception should be thrown.');

        /** @var Location $location */
        foreach ($result as $location) {
            $this->assertInstanceOf(
                Location::class,
                $location,
                'All items in Geocoder\Collection must implement Geocoder\Location'
            );

            $this->assertInstanceOf(
                AdminLevelCollection::class,
                $location->getAdminLevels(),
                'Location::getAdminLevels MUST always return a AdminLevelCollection'
            );
            $arrayData = $location->toArray();
            $this->assertTrue(is_array($arrayData), 'Location::toArray MUST return an array.');
            $this->assertNotEmpty($arrayData, 'Location::toArray cannot be empty.');

            // Verify coordinates
            if (null !== $coords = $location->getCoordinates()) {
                $this->assertInstanceOf(
                    Coordinates::class,
                    $coords,
                    'Location::getCoordinates MUST always return a Coordinates or null'
                );

                // Using "assertNotEmpty" means that we can not have test code where coordinates is on equator or long = 0
                $this->assertNotEmpty($coords->getLatitude(), 'If coordinate object exists it cannot have an empty latitude.');
                $this->assertNotEmpty($coords->getLongitude(), 'If coordinate object exists it cannot have an empty longitude.');
            }

            // Verify bounds
            if (null !== $bounds = $location->getBounds()) {
                $this->assertInstanceOf(
                    Bounds::class,
                    $bounds,
                    'Location::getBounds MUST always return a Bounds or null'
                );

                // Using "assertNotEmpty" means that we can not have test code where coordinates is on equator or long = 0
                $this->assertNotEmpty($bounds->getSouth(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getWest(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getNorth(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getEast(), 'If bounds object exists it cannot have an empty values.');
            }

            // Check country
            if (null !== $country = $location->getCountry()) {
                $this->assertInstanceOf(
                    Country::class,
                    $country,
                    'Location::getCountry MUST always return a Country or null'
                );
                $this->assertFalse(null === $country->getCode() && null === $country->getName(), 'Both code and name cannot be empty');

                if (null !== $country->getCode()) {
                    $this->assertNotEmpty(
                        $location->getCountry()->getCode(),
                        'The Country should not have an empty code.'
                    );
                }

                if (null !== $country->getName()) {
                    $this->assertNotEmpty(
                        $location->getCountry()->getName(),
                        'The Country should not have an empty name.'
                    );
                }
            }
        }
    }
}
