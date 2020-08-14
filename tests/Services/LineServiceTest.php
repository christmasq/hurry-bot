<?php

namespace Tests\Services;

use App\Services\Line\Client;
use App\Services\LineService;
use LINE\LINEBot\Constant\ActionType;
use LINE\LINEBot\Constant\MessageType;
use Tests\TestCase;

class LineServiceTest extends TestCase
{
    protected $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('setReplyToken')->andReturn(true);
            $mock->shouldReceive('replyMessage')->andReturn(true);
            $mock->shouldReceive('replyText')->andReturn(true);
        });

        $this->service = app(LineService::class);
    }

    public function handleEventProvider()
    {
        $postback_data = [
            'data' => '',
            'params' => '',
        ];

        $text_message = [
            'type' => MessageType::TEXT,
            'text' => 'test',
        ];

        $location_message = [
            'type' => MessageType::LOCATION,
            'title' => 'test',
            'address' => 'test',
            'latitude' => 0,
            'longitude' => 0,
        ];

        return [
            'text message event' => [ActionType::MESSAGE, $text_message],
            'location message event' => [ActionType::MESSAGE, $location_message],
            'other message event' => [ActionType::MESSAGE, ['type' => 'other']],
            'postback event' => [ActionType::POSTBACK, $postback_data],
            'other event' => ['other', ['type' => 'other']],
        ];
    }

    /**
     * @dataProvider handleEventProvider
     * @param string $event_type
     * @param array $message
     */
    public function testHandleEvent($event_type, $message)
    {
        $event = $this->createEvent($event_type, $message);
        $this->service->handleEvent([$event]);
        $this->assertTrue(true);
    }

    public function replyMessageByTextProvider()
    {
        return [
            ["show command"],
            ["show text"],
            ["show image"],
            ["show sticker"],
            ["show location"],
            ["show video"],
            ["show audio"],
            ["show multiple message"],
            ["show flex"],
            ["show flex2"],
        ];
    }

    /**
     * @dataProvider replyMessageByTextProvider
     * @param string $text
     */
    public function testReplyMessageByText($text)
    {
        $this->service->replyMessageByText($text);
    }
}
