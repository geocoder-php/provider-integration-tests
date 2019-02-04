<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

declare(strict_types=1);

namespace Geocoder\IntegrationTest;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/*
 * This test is not 100% perfect. You may have installed HTTPlug:1.x and PSR-18.
 */
if (\interface_exists(ClientInterface::class)) {
    /**
     * @internal code for php-http/httplug:2.x
     */
    trait HttpClientTrait
    {
        abstract protected function doSendRequest(RequestInterface $request);

        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            return $this->doSendRequest($request);
        }
    }
} else {
    /**
     * @internal code for php-http/httplug:1.x
     */
    trait HttpClientTrait
    {
        abstract protected function doSendRequest(RequestInterface $request);

        public function sendRequest(RequestInterface $request)
        {
            return $this->doSendRequest($request);
        }
    }
}
