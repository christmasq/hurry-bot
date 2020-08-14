<?php


namespace App\Services;


use App\Services\Line\Builder\Message;
use App\Services\Line\Client;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Constant\ActionType;
use LINE\LINEBot\Constant\Flex\ComponentAlign;
use LINE\LINEBot\Constant\Flex\ComponentImageSize;
use LINE\LINEBot\Constant\MessageType;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class LineService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * handle event
     * @param BaseEvent[] $events
     * @throws \LINE\LINEBot\Exception\InvalidEventSourceException
     */
    public function handleEvent(array $events)
    {
        foreach ($events as $event) {
            switch ($event->getType()) {
                case ActionType::MESSAGE:
                    $this->handleMessageEvent($event);
                    break;
                case ActionType::POSTBACK:
                    $this->handlePostbackEvent($event);
                    break;
                default:
                    Log::warning('Unsupported event type: ' . $event->getType());
            }
        };
    }

    /**
     * handle message event
     * @param MessageEvent $event
     * @return TemplateMessageBuilder|TextMessageBuilder|string
     * @throws GuzzleException
     * @throws LINEBot\Exception\InvalidEventSourceException
     * @throws \LINE\LINEBot\Exception\InvalidEventSourceException
     */
    public function handleMessageEvent(MessageEvent $event)
    {
        switch ($event->getMessageType()) {
            case MessageType::TEXT:
                $this->client->setReplyToken($event->getReplyToken());
                $this->handleTextMessageEvent($event);
                break;
            case MessageType::LOCATION:
                $this->client->setReplyToken($event->getReplyToken());
                $this->handleLocationMessageEvent($event);
                break;
            default:
                Log::warning('Unsupported message type: ' . $event->getMessageType());
        }
    }

    /**
     * handle text message event
     * @param TextMessage $event
     * @throws \LINE\LINEBot\Exception\InvalidEventSourceException
     */
    public function handleTextMessageEvent(TextMessage $event)
    {
        // get event info (You can use the ids to identify user or group)
        $text = trim($event->getText());
        $user_id = $event->getUserId();
        $group_id = ($event->isGroupEvent()) ? $event->getGroupId() : null;
        $room_id = ($event->isRoomEvent()) ? $event->getRoomId() : null;

        // reply message by text (example method)
        $this->replyMessageByText($text);
    }

    /**
     * handle location message event
     * @param LocationMessage $event
     */
    public function handleLocationMessageEvent(LocationMessage $event)
    {
        // get event info (the location info of user marks)
        $user_id = $event->getUserId();
        $address = $event->getAddress();
        $latitude = $event->getLatitude();
        $longitude = $event->getLongitude();
        $title = $event->getTitle();

        // reply message example
        $reply_text = "Location: " . $title . "\n" . $address . "\n" . "(" . $latitude . ", " . $longitude . ")";
        $this->client->replyText($reply_text);
    }

    /**
     * handle postback event
     * @param PostbackEvent $event
     */
    public function handlePostbackEvent(PostbackEvent $event)
    {
        // get event info
        $user_id = $event->getUserId();
        $data = [];
        parse_str($event->getPostbackData(), $data);
        $params = $event->getPostbackParams();

        // reply message example
        $this->client->replyText('test');
    }

    /**
     * reply messages by text of user input
     * FIXME Provide the examples of multiple case.
     * FIXME You can set the keyword of command and adjust codes for reply specific actions.
     * @param string $text user input text
     */
    public function replyMessageByText($text)
    {
        // set example info (example files in the `public/example` folder)
        $example_text = 'hello';
        $example_image_url = config('app.url') . '/example/hi-there.jpg';
        $example_video_url = config('app.url') . '/example/example.mp4';
        $example_preview_image_url = config('app.url') . '/example/example.png';
        $example_audio_url = config('app.url') . '/example/example.m4a';

        // construct builder by text keyword
        $message = new Message();
        switch ($text) {
            case 'show command':
                $reply_text = "Commands:\n" .
                    "show command\n" .
                    "show text\n" .
                    "show image\n" .
                    "show sticker\n" .
                    "show location\n" .
                    "show video\n" .
                    "show audio\n" .
                    "show multiple message\n" .
                    "show flex\n" .
                    "show flex2";

                $message->addBuilder(Message::text($reply_text));
                break;
            case 'show text':
                $message->addBuilder(Message::text($example_text));
                break;
            case 'show image':
                // JPG, JPEG or PNG
                $message->addBuilder(Message::image($example_image_url));
                break;
            case 'show sticker':
                // available sticker list: https://developers.line.biz/media/messaging-api/sticker_list.pdf
                $package_id = '11538';
                $sticker_id = '51626526';
                $message->addBuilder(Message::sticker($package_id, $sticker_id));
                break;
            case 'show location':
                // the example of location
                $title = 'my location';
                $address = '〒150-0002 東京都渋谷区渋谷２丁目２１−１';
                $lat = 35.65910807942215;
                $log = 139.70372892916203;
                $message->addBuilder(Message::location($title, $address, $lat, $log));
                break;
            case 'show video':
                // must be mp4 format
                $message->addBuilder(Message::video($example_video_url, $example_preview_image_url));
                break;
            case 'show audio':
                // Only M4A files are supported
                $duration = 60000; // Length of audio file (milliseconds)
                $message->addBuilder(Message::audio($example_audio_url, $duration));
            case 'show multiple message':
                // the example of multiple type messages
                $contents = [
                    ['type' => MessageType::TEXT, 'text' => $example_text],
                    ['type' => MessageType::IMAGE, 'url' => $example_image_url],
                ];
                $message->addMultiMessageBuilders($contents);
                break;
            case 'show flex':
                // put the file in the `resource/flex` path
                $json = file_get_contents(resource_path("flex/example.json"));

                // example of five bubble containers
                $data_var = [];
                for ($i = 0;$i < 5;++$i) {
                    // set values of json file variables
                    $var = [
                        'header_text' => 'Header' . $i,
                        'hero_image_url' => $example_image_url,
                        'body_text' => 'Body' . $i,
                        'button_label' => 'Click',
                        'button_url' => 'https://www.google.com',
                    ];
                    $data_var[] = $var;
                }

                $message->addBuilder(Message::flex($json, $data_var, 'Example'));
                break;
            case 'show flex2':
                $json = file_get_contents(resource_path("flex/example2.json"));

                // example of five bubble containers
                $data_var = [];
                for ($i = 0;$i < 5;++$i) {
                    // set values of json file variables by line-bot-sdk builder class
                    $header = [];
                    $header[] = TextComponentBuilder::builder()
                        ->setText('Header' . $i)
                        ->setAlign(ComponentAlign::CENTER)
                        ->build();

                    $header[] = ImageComponentBuilder::builder()
                        ->setUrl($example_image_url)
                        ->setSize(ComponentImageSize::SM)
                        ->build();

                    $body = [];
                    $body[] = TextComponentBuilder::builder()
                        ->setText('Body' . $i)
                        ->setAlign(ComponentAlign::CENTER)
                        ->build();

                    $footer = [];
                    $footer_action = new UriTemplateActionBuilder(
                        'Click' . $i,
                        'https://www.google.com'
                    );
                    $footer[] = ButtonComponentBuilder::builder()
                        ->setAction($footer_action)
                        ->build();

                    $var = [
                        'header_contents' => json_encode($header),
                        'hero_image_url' => $example_image_url,
                        'body_contents' => json_encode($body),
                        'footer_contents' => json_encode($footer),
                    ];
                    $data_var[] = $var;
                }

                $message->addBuilder(Message::flex($json, $data_var, 'Example'));
                break;
        }

        // reply message by builders
        $this->client->replyMessage($message->getBuilders());
    }
}
