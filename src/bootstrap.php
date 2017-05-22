<?php

require_once __DIR__.'/../vendor/autoload.php';

\Http\Discovery\ClassDiscovery::prependStrategy('\Nyholm\Psr7\Httplug\DiscoveryStrategy');
