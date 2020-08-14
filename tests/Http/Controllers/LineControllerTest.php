<?php

namespace Tests\Http\Controllers;

use App\Services\Line\Client;
use LINE\LINEBot\Constant\ActionType;
use LINE\LINEBot\Constant\MessageType;
use Tests\TestCase;

class LineControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testPushMessage()
    {
        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('pushMessage')->andReturn(true);
        });

        $params = [
            'to' => 'user_id',
            'contents' => '[{"type":"text", "text":"Hi"},{"type":"text", "text":"This is bot."}]',
        ];
        $response = $this->post('/api/line/push-message', $params);
        $response->assertStatus(200);
    }

    public function testPushMessageException()
    {
        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('pushMessage')->andThrow(new \Exception('test'));
        });

        $params = [
            'to' => 'user_id',
            'contents' => '[{"type":"text", "text":"Hi"},{"type":"text", "text":"This is bot."}]',
        ];
        $response = $this->post('/api/line/push-message', $params);
        $response->assertStatus(200);
    }

    public function testWebhook()
    {
        $event = $this->createEvent(ActionType::MESSAGE, ['type' => MessageType::TEXT, 'text' => 'test']);
        $this->mock(Client::class, function ($mock) use ($event) {
            $mock->shouldReceive('parseEvent')->andReturn([$event]);
            $mock->shouldReceive('replyMessage')->andReturn(true);
            $mock->shouldReceive('pushMessage')->andReturn(true);
            $mock->shouldReceive('setReplyToken')->andReturn(true);
            $mock->shouldReceive('replyText')->andReturn(true);
        });

        $response = $this->post('/api/line/webhook', []);
        $response->assertStatus(200);
    }

    public function testWebhookException()
    {
        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('parseEvent')->andThrow(new \Exception('test'));
        });

        $response = $this->post('/api/line/webhook', []);
        $response->assertStatus(200);
    }
}
