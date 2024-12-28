<?php

namespace Klevio\KlevioApi\Tests;

use Klevio\KlevioApi\KlevioApi;
use Klevio\KlevioApi\Facades\KlevioApi as KlevioApiFacade;

class KlevioApiServiceProviderTest extends TestCase
{
    /** @test */
    public function it_can_resolve_klevio_api_from_container()
    {
        $api = $this->app->make('klevio-api');
        
        $this->assertInstanceOf(KlevioApi::class, $api);
    }

    /** @test */
    public function it_registers_facade()
    {
        $this->assertTrue(class_exists(KlevioApiFacade::class));
        
        $api = KlevioApiFacade::getFacadeRoot();
        
        $this->assertInstanceOf(KlevioApi::class, $api);
    }

    /** @test */
    public function it_merges_config()
    {
        $config = $this->app['config']->get('klevio-api');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('client_id', $config);
        $this->assertArrayHasKey('api_key', $config);
        $this->assertArrayHasKey('private_key', $config);
        $this->assertArrayHasKey('public_key', $config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('timeout', $config);
    }
}
