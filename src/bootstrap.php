<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once __DIR__.'/../vendor/autoload.php';

\Http\Discovery\ClassDiscovery::prependStrategy('\Nyholm\Psr7\Httplug\DiscoveryStrategy');
