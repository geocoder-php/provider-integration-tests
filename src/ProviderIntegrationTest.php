<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\IntegrationTests;

use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class ProviderIntegrationTest extends TestCase
{
    /**
     * @var array with functionName => reason
     */
    protected $skippedTests = [];

    /**
     * @return Provider that is used in the tests
     */
    abstract protected function createProvider(HttpClient $httpClient);

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
}
