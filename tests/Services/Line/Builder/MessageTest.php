<?php

namespace Tests\Services\Line\Builder;

use App\Services\Line\Builder\Message;
use LINE\LINEBot\Constant\MessageType;
use Tests\TestCase;

class MessageTest extends TestCase
{
    /**
     * test all case by addMultiMessageBuilders
     */
    public function testAddMultiMessageBuilders()
    {
        $message = new Message();
        $message->addMultiMessageBuilders([
            'text' => ['type' => MessageType::TEXT, 'text' => 'test'],
            'image' => ['type' => MessageType::IMAGE, 'url' => 'test'],
            'sticker' => ['type' => MessageType::STICKER, 'package_id' => 0, 'sticker_id' => 0],
            'location' => ['type' => MessageType::LOCATION, 'title' => 'test', 'address' => 'test', 'latitude' => 0, 'longitude' => 0],
            'flex' => ['type' => MessageType::FLEX, 'json' => 'test', 'data_vars' => [], 'alt_text' => 'test'],
            'video' => ['type' => MessageType::VIDEO, 'url' => 'test', 'preview_image_url' => 'test'],
            'audio' => ['type' => MessageType::AUDIO, 'url' => 'test', 'duration' => 60000],
            'error type' => ['type' => 'error type', ],
            'miss fields' => ['type' => MessageType::TEXT],
        ]);

        $expected = [
            ['type' => MessageType::TEXT, 'text' => 'test'],
            ['type' => MessageType::IMAGE, 'originalContentUrl' => 'test', 'previewImageUrl' => 'test'],
            ['type' => MessageType::STICKER, 'packageId' => 0, 'stickerId' => 0],
            ['type' => MessageType::LOCATION, 'title' => 'test', 'address' => 'test', 'latitude' => 0, 'longitude' => 0],
            ['type' => MessageType::FLEX, 'altText' => 'test', 'contents' => ['type' => 'carousel', 'contents' => []]],
            ['type' => MessageType::VIDEO, 'originalContentUrl' => 'test', 'previewImageUrl' => 'test'],
            ['type' => MessageType::AUDIO, 'originalContentUrl' => 'test', 'duration' => 60000],
        ];

        $this->assertEquals($expected, $message->getBuilders()->buildMessage());
    }
}
