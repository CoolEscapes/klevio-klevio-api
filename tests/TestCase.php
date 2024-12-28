<?php

namespace Klevio\KlevioApi\Tests;

use Klevio\KlevioApi\KlevioApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            KlevioApiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('klevio-api.client_id', 'test-client-id');
        $app['config']->set('klevio-api.api_key', 'test-api-key');
        $app['config']->set('klevio-api.private_key', $this->getTestPrivateKey());
        $app['config']->set('klevio-api.public_key', $this->getTestPublicKey());
        $app['config']->set('klevio-api.base_url', 'https://api.klevio.test/v2');
        $app['config']->set('klevio-api.timeout', 30);
    }

    protected function getTestPrivateKey(): string
    {
        return <<<EOD
-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIH4gyo6eV6dxLpzX4FxlC+qBzPX/83BLziCBPEoTbdyeoAoGCCqGSM49
AwEHoUQDQgAEX5PpHPHW9QGZqVG4ZV2jZk0jn4p0R6BsXmPYB0oHkUeGM69Qr0JF
0P/HBtV9q7yVvQrwXjVUH4CQp1B+5RZzQw==
-----END EC PRIVATE KEY-----
EOD;
    }

    protected function getTestPublicKey(): string
    {
        return <<<EOD
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEX5PpHPHW9QGZqVG4ZV2jZk0jn4p0
R6BsXmPYB0oHkUeGM69Qr0JF0P/HBtV9q7yVvQrwXjVUH4CQp1B+5RZzQw==
-----END PUBLIC KEY-----
EOD;
    }
}
