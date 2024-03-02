# Geocoder integration tests

[![Latest Stable Version](https://poser.pugx.org/geocoder-php/provider-integration-tests/v/stable)](https://packagist.org/packages/geocoder-php/provider-integration-tests)
[![Total Downloads](https://poser.pugx.org/geocoder-php/provider-integration-tests/downloads)](https://packagist.org/packages/geocoder-php/provider-integration-tests)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/provider-integration-tests/d/monthly.png)](https://packagist.org/packages/geocoder-php/provider-integration-tests)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This repository contains integration tests to make sure your implementation of a Geocoder Provider is correct.

### Install

```bash
composer require --dev geocoder-php/provider-integration-tests:dev-master
```

### Use

Create a test that looks like this:

```php
use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Psr\Http\Client\ClientInterface;

class IntegrationTest extends ProviderIntegrationTest
{
    protected function createProvider(ClientInterface $httpClient)
    {
        return new GoogleMaps($httpClient);
    }

    protected function getCacheDir(): string;
    {
        return dirname(__DIR__).'/.cached_responses';
    }

    protected function getApiKey(): string;
    {
        return env('GOOGLE_API_KEY');
    }
}
```
