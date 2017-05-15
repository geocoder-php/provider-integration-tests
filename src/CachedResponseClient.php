<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\IntegrationTest;

use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;

/**
 * Serve responses from local file cache
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CachedResponseClient implements HttpClient
{
    /**
     * @var HttpClient
     */
    private $delegate;

    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param HttpClient  $delegate
     * @param string      $cacheDir
     * @param string|null $apiKey
     */
    public function __construct(HttpClient $delegate, $cacheDir, $apiKey = null)
    {
        $this->delegate = $delegate;
        $this->cacheDir = $cacheDir;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $url = (string) $request->getUri();
        $host = (string) $request->getUri()->getHost();
        if ($this->apiKey) {
            $url = str_replace($this->apiKey, '[apikey]', $url);
        }

        $file = sprintf('%s/%s/%s_%s', __DIR__, $this->cacheDir, $host, sha1($url));
        if (is_file($file) && is_readable($file)) {
            return new Response(200, [], Psr7\stream_for(unserialize(file_get_contents($file))));
        }

        $response = $this->delegate->sendRequest($request);
        file_put_contents($file, serialize($response->getBody()->getContents()));

        return $response;
    }
}
