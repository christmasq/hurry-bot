<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Constant\ActionType;
use LINE\LINEBot\Constant\EventSourceType;
use LINE\LINEBot\Constant\MessageType;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Log::shouldReceive('emergency', 'critical', 'error', 'debug', 'warning', 'alert', 'info')
            ->andReturn(true);
    }

    /**
     * create event object
     * @param string $event_type
     * @param array $message
     * @return object
     */
    protected function createEvent($event_type, $message)
    {
        // initialize content array
        $content = [
            'type' => $event_type,
            'source' => [
                'type' => EventSourceType::USER,
                'userId' => 'test',
            ],
            'replyToken' => ''
        ];

        switch ($event_type) {
            case ActionType::POSTBACK:
                $content['postback'] = $message;
                return new PostbackEvent($content);
                break;
            case ActionType::MESSAGE:
            default:
                $content['message'] = $message;
                $event = $this->createMessageEvent($content, $message);
        }

        return $event;
    }

    /**
     * create message event
     * @param array $content
     * @param array $message
     * @return MessageEvent|LocationMessage|TextMessage
     */
    protected function createMessageEvent($content, $message)
    {
        switch ($message['type']) {
            case MessageType::TEXT:
                $event = new TextMessage($content);
                break;
            case MessageType::LOCATION:
                $event = new LocationMessage($content);
                break;
            default:
                $event = new MessageEvent($content);
        }

        return $event;
    }
}
