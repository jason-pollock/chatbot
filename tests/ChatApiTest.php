<?php

namespace CoolGuy\WordPress\Plugin\ChatBot\Tests;

use CoolGuy\WordPress\Plugin\ChatBot\ChatApi;
use PHPUnit\Framework\TestCase;
use Mockery;
use WP_Mock;

class ChatApiTest extends TestCase
{
    protected ChatApi $chatApi;

    public static function setUpBeforeClass(): void
    {
        WP_Mock::bootstrap();
    }

    protected function setUp(): void
    {
        parent::setUp();
        WP_Mock::setUp();
        $this->chatApi = new ChatApi();

        WP_Mock::userFunction('WP_REST_Response', [
            'return' => function ($data = null, $status = 200) {
                return (object) ['data' => $data, 'status' => $status];
            }
        ]);
    }

    protected function tearDown(): void
    {
        WP_Mock::tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateRoute(): void
    {
        $endpoint = '/test';
        $method = 'GET';
        $callback = function () {
        };
        $permission_callback = function () {
            return __return_true();
        };

        WP_Mock::expectActionAdded('rest_api_init', Mockery::type('callable'));

        $this->chatApi->createRoute($endpoint, $method, $callback, $permission_callback);
    }

    public function testInitializeChat(): void
    {
        $request = Mockery::mock(\WP_REST_Request::class);
        $request->shouldReceive('get_param')
            ->with('articleContent')
            ->andReturn('Test article content');

        $responseMock = Mockery::mock('overload:WP_REST_Response');
        $responseMock->shouldReceive('get_data')
        ->andReturn('Chat initialized with context.');
        $responseMock->shouldReceive('get_status')
        ->andReturn(200);
    
        $response = $this->chatApi->initializeChat($request);
    
        $this->assertEquals('Chat initialized with context.', $response->get_data());
        $this->assertEquals(200, $response->get_status());
    }

    public function testHandleMessage(): void
    {
        $_ENV['OPENAI_API_KEY'] = 'test';

        $request = Mockery::mock(\WP_REST_Request::class);
        $request->shouldReceive('get_param')
            ->with('message')
            ->andReturn('Test message');

        WP_Mock::userFunction('wp_remote_post', [
            'return' => function ($url, $args) {
                return [
                    'response' => [
                        'code' => 200,
                        'message' => 'OK'
                    ],
                    'body' => json_encode([
                        'choices' => [
                            [
                                'text' => 'Test response'
                            ]
                        ]
                    ]),
                    'context' => 'Test context'
                ];
            }
        ]);

        WP_Mock::userFunction('is_wp_error', ['return' => false]);

        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);

        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return' => function ($response) {
                return $response['body'];
            }
        ]);

        $response = $this->chatApi->handleMessage($request);

        $this->assertInstanceOf(\WP_REST_Response::class, $response);
    }
}
