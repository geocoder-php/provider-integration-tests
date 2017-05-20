<?php

namespace Geocoder\IntegrationTest;


use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use Http\Mock\Client as MockedHttpClient;
use Psr\Http\Message\RequestInterface;
use Http\Client\Curl\Client as HttplugClient;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Get a real HTTP client. If $_SERVER['RESPONSE_CACHE'] is set to a path it will use cached responses.
     *
     * @return HttpClient
     */
    protected function getHttpClient($apiKey = null)
    {
        if (isset($_SERVER['RESPONSE_CACHE']) && false !== $_SERVER['RESPONSE_CACHE']) {
            return new CachedResponseClient(new HttplugClient(), $_SERVER['RESPONSE_CACHE'], $apiKey);
        } else {
            return new HttplugClient();
        }
    }

    /**
     * Get a mocked HTTP client that never do calls over the internet. Use this is you want to control the response data.
     *
     * @param string|null $body
     * @param int         $statusCode
     *
     * @return HttpClient
     */
    protected function getMockedHttpClient($body = null, $statusCode = 200)
    {
        $client = new MockedHttpClient();
        $client->addResponse(new Response($statusCode, [], $body));

        return $client;
    }

    /**
     * Get a mocked HTTP client where you may do tests on the request.
     *
     * @param string|null $body
     * @param int         $statusCode
     *
     * @return HttpClient
     */
    protected function getMockedHttpClientCallback(callable $requestCallback)
    {
        $client = $this->getMockBuilder(HttpClient::class)->getMock();

        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($requestCallback) {
                $response = $requestCallback($request);

                if (!$response instanceof ResponseInterface) {
                    $response = new Response(200, [], (string) $response);
                }

                return $response;
            });

        return $client;
    }
}
