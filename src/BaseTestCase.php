<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\IntegrationTest;

use Http\Client\Curl\Client as HttplugClient;
use Http\Client\HttpClient;
use Http\Mock\Client as MockedHttpClient;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * @return string|null the directory where cached responses are stored
     */
    abstract protected function getCacheDir();

    /**
     * Get a real HTTP client. If a cache dir is set to a path it will use cached responses.
     *
     * @return HttpClient
     */
    protected function getHttpClient($apiKey = null)
    {
        if (null !== $cacheDir = $this->getCacheDir()) {
            return new CachedResponseClient(new HttplugClient(), $cacheDir, $apiKey);
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
