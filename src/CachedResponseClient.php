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
use Nyholm\Psr7\Factory\HttplugFactory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Serve responses from local file cache.
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
     * @var null|string
     */
    private $appCode;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param HttpClient  $delegate
     * @param string      $cacheDir
     * @param string|null $apiKey
     * @param string|null $appCode
     */
    public function __construct(HttpClient $delegate, $cacheDir, $apiKey = null, $appCode = null)
    {
        $this->delegate = $delegate;
        $this->cacheDir = $cacheDir;
        $this->apiKey = $apiKey;
        $this->appCode = $appCode;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $host = (string) $request->getUri()->getHost();
        $cacheKey = (string) $request->getUri();
        if ('POST' === $request->getMethod()) {
            $cacheKey .= $request->getBody();
        }
        if (!empty($this->apiKey)) {
            $cacheKey = str_replace($this->apiKey, '[apikey]', $cacheKey);
        }
        if (!empty($this->appCode)) {
            $cacheKey = str_replace($this->appCode, '[appCode]', $cacheKey);
        }

        $file = sprintf('%s/%s_%s', $this->cacheDir, $host, sha1($cacheKey));
        if (is_file($file) && is_readable($file)) {
            return new Response(200, [], (new HttplugFactory())->createStream(unserialize(file_get_contents($file))));
        }

        $response = $this->delegate->sendRequest($request);
        file_put_contents($file, serialize($response->getBody()->getContents()));

        return $response;
    }
}
