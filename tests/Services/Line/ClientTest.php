<?php

namespace Tests\Services\Line;

use App\Services\Line\Builder\Message;
use App\Services\Line\Client;
use LINE\LINEBot;
use Tests\TestCase;

class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function mockResponse($result = true)
    {
        return $this->mock(LINEBot\Response::class, function ($mock) use ($result) {
            $mock->shouldReceive('isSucceeded')->andReturn($result);
            $mock->shouldReceive('getRawBody')->andReturn('{"test":"test"}');
            $mock->shouldReceive('getJSONDecodedBody')->andReturn(['test' => 'test']);
        });
    }

    public function testParseEvent()
    {
        $line_bot = $this->mock(LINEBot::class, function ($mock) {
            $mock->shouldReceive('parseEventRequest')->andReturn(true);
        });

        $client = new Client($line_bot);
        $this->assertTrue($client->parseEvent('', ''));
    }

    public function testReplyMessage()
    {
        $response = $this->mockResponse();

        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('replyMessage')->andReturn($response);
        });

        $client = new Client($line_bot);
        $client->setReplyToken('');
        $this->assertTrue($client->replyMessage(Message::text('text')));
        // builder is empty
        $this->assertFalse($client->replyMessage(null));
        // by type
        $this->assertTrue($client->replyText('text'));
        $this->assertTrue($client->replyImage('url'));
        $this->assertTrue($client->replySticker(0, 0));
        $this->assertTrue($client->replyVideo('url', 'url'));
    }

    public function testReplyMessageResponseFailed()
    {
        $response = $this->mockResponse(false);

        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('replyMessage')->andReturn($response);
        });

        $client = new Client($line_bot);
        $client->setReplyToken('');
        $this->assertFalse($client->replyMessage(Message::text('text')));
    }

    public function testPushMessage()
    {
        $response = $this->mockResponse();

        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('pushMessage')->andReturn($response);
        });

        $client = new Client($line_bot);
        $client->setReplyToken('');
        $this->assertTrue($client->pushMessage('test', Message::text('text')));
        // builder is empty
        $this->assertFalse($client->pushMessage('test', null));
        // by type
        $this->assertTrue($client->pushText('test', 'test'));
        $this->assertTrue($client->pushImage('test', 'url'));
    }

    public function testPushMessageReponseFailed()
    {
        $response = $this->mockResponse(false);

        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('pushMessage')->andReturn($response);
        });

        $client = new Client($line_bot);
        $client->setReplyToken('');
        $this->assertFalse($client->pushMessage('test', Message::text('text')));
    }

    public function testGetUserProfile()
    {
        $response = $this->mockResponse();

        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('getProfile')->andReturn($response);
        });

        $client = new Client($line_bot);
        $this->assertEquals(['test' => 'test'], $client->getUserProfile('user_id'));
    }

    public function testGetGroupMemberProfile()
    {
        $response = $this->mockResponse();

        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('getGroupMemberProfile')->andReturn($response);
        });

        $client = new Client($line_bot);
        $this->assertEquals(['test' => 'test'], $client->getGroupMemberProfile('group_id', 'user_id'));
    }

    public function testLeaveGroup()
    {
        $response = $this->mockResponse();
        $line_bot = $this->mock(LINEBot::class, function ($mock) use ($response) {
            $mock->shouldReceive('replyMessage')->andReturn($response);
            $mock->shouldReceive('leaveRoom')->andReturn(true);
            $mock->shouldReceive('leaveGroup')->andReturn(true);
        });

        $client = new Client($line_bot);
        $this->assertTrue($client->leaveGroup('group_id', true));
        $this->assertTrue($client->leaveGroup('group_id', false));
    }
}
