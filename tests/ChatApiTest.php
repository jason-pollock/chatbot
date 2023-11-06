<?php

namespace CoolGuy\WordPress\Plugin\ChatBot\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use WP_Mock;
use CoolGuy\WordPress\Plugin\ChatBot\ChatApi;
use WP_REST_Request;
use WP_REST_Response;

class ChatApiTest extends TestCase
{
    protected ChatApi $chatApi;

    protected function setUp(): void
    {
        WP_Mock::setUp();
        $this->chatApi = new ChatApi();
    }

    protected function tearDown(): void
    {
        WP_Mock::tearDown();
        Mockery::close();
    }

    public function testCreateRoute(): void
    {
        $endpoint = '/test';
        $method = 'GET';
        $callback = function () {
        };
        $permission_callback = function () {
            return true;
        };

        WP_Mock::userFunction('add_action', [
            'times' => 1,
            'args' => ['rest_api_init', Mockery::type('callable')]
        ]);

        WP_Mock::userFunction('register_rest_route', [
            'times' => 1,
            'args' => [ChatApi::REST_NAMESPACE, $endpoint, [
                'methods' => $method,
                'callback' => $callback,
                'permission_callback' => $permission_callback,
            ]]
        ]);

        $this->chatApi->createRoute($endpoint, $method, $callback, $permission_callback);
    }

    public function testInitializeChat(): void
    {
        $request = Mockery::mock(WP_REST_Request::class);
        $request->shouldReceive('get_param')
            ->with('articleContent')
            ->andReturn('Test article content');

        $response = $this->chatApi->initializeChat($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals('Chat initialized with context.', $response->get_data());
        $this->assertEquals(200, $response->get_status());
    }

    public function testHandleMessage(): void
    {
        $request = Mockery::mock(WP_REST_Request::class);
        $request->shouldReceive('get_param')
            ->with('message')
            ->andReturn('Test message');

        $response = $this->chatApi->handleMessage($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
    }
}
