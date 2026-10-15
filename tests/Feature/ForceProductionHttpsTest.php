<?php

namespace Tests\Feature;

use Tests\TestCase;

class ForceProductionHttpsTest extends TestCase
{
    public function test_production_http_request_redirects_to_https(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://bagspar.se']);

        $response = $this->get('http://bagspar.se/demo');

        $response->assertStatus(301);
        $this->assertSame('https://bagspar.se/demo', $response->headers->get('Location'));
    }

    public function test_production_http_post_keeps_the_method_on_the_https_url(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://bagspar.se']);

        $response = $this->post('http://bagspar.se/intresse', [
            'company' => 'Båghantverket',
            'email' => 'verkstad@example.test',
        ]);

        $response->assertStatus(308);
        $this->assertSame('https://bagspar.se/intresse', $response->headers->get('Location'));
    }

    public function test_production_request_forwarded_as_https_is_not_redirected(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://bagspar.se']);

        $response = $this->withServerVariables([
            'HTTPS' => 'off',
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('http://bagspar.se/');

        $response->assertOk();
    }

    public function test_production_request_on_localhost_is_not_redirected(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://bagspar.se']);

        $this->get('/')->assertOk();
    }

    public function test_http_request_is_left_alone_outside_production(): void
    {
        $this->get('http://bagspar.se/')->assertOk();
    }
}
