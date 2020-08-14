<?php


namespace App\Services\Line\Builder;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot\Constant\MessageType;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;

/**
 * Provide line MessageBuilder component
 * @package App\Services\Line\Builder
 */
class Message
{
    /**
     * @var MultiMessageBuilder
     */
    protected $builders;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->builders = new MultiMessageBuilder();
    }

    /**
     * check whether multi builder is empty or other judgement
     * @return bool
     */
    protected function checkBuilders()
    {
        return (count($this->builders->buildMessage()) > 0);
    }

    /**
     * add builder to builders
     * @param MessageBuilder $builder
     */
    public function addBuilder($builder)
    {
        $this->builders->add($builder);
    }

    /**
     * return builders or null if is empty
     * @return MultiMessageBuilder|null
     */
    public function getBuilders()
    {
        return $this->checkBuilders() ? $this->builders : null;
    }

    /**
     * add multiple message builders by content array
     * @param array $contents
     * @return void
     */
    public function addMultiMessageBuilders($contents = [])
    {
        try {
            foreach ($contents as $content) {
                switch ($content['type']) {
                    case MessageType::TEXT:
                        $this->addBuilder(static::text($content['text']));
                        break;
                    case MessageType::IMAGE:
                        $this->addBuilder(static::image($content['url']));
                        break;
                    case MessageType::STICKER:
                        $this->addBuilder(static::sticker($content['package_id'], $content['sticker_id']));
                        break;
                    case MessageType::LOCATION:
                        $this->addBuilder(static::location($content['title'], $content['address'], $content['latitude'], $content['longitude']));
                        break;
                    case MessageType::FLEX:
                        $this->addBuilder(static::flex($content['json'], $content['data_vars'], $content['alt_text']));
                        break;
                    case MessageType::VIDEO:
                        $this->addBuilder(static::video($content['url'], $content['preview_image_url']));
                        break;
                    case MessageType::AUDIO:
                        $this->addBuilder(static::audio($content['url'], $content['duration']));
                    default:
                        Log::warning('not supported message type');
                }
            }
        } catch (\Exception $e) {
            Log::debug(__FUNCTION__ . ' exception: ' . $e->getMessage());
        }
    }

    /**
     * @param string $text
     * @return TextMessageBuilder
     */
    public static function text($text)
    {
        return new TextMessageBuilder($text);
    }

    /**
     * @param string $url
     * @param string $preview_image_url
     * @return ImageMessageBuilder
     */
    public static function image($url, $preview_image_url = '')
    {
        $preview_image_url = $preview_image_url ?: $url;
        return new ImageMessageBuilder($url, $preview_image_url);
    }

    /**
     * @param string $url
     * @param string $preview_image_url
     * @return VideoMessageBuilder
     */
    public static function video($url, $preview_image_url)
    {
        return new VideoMessageBuilder($url, $preview_image_url);
    }

    /**
     * @param string $url
     * @param int $duration
     * @return AudioMessageBuilder
     */
    public static function audio($url, $duration)
    {
        return new AudioMessageBuilder($url, $duration);
    }

    /**
     * @param int $package_id
     * @param int $sticker_id
     * @return StickerMessageBuilder
     */
    public static function sticker($package_id, $sticker_id)
    {
        return new StickerMessageBuilder($package_id, $sticker_id);
    }

    /**
     * @param string $title
     * @param string $address
     * @param double $latitude
     * @param double $longitude
     * @return LocationMessageBuilder
     */
    public static function location($title, $address, $latitude, $longitude)
    {
        return new LocationMessageBuilder($title, $address, $latitude, $longitude);
    }

    /**
     * @param string $json
     * @param array $data_vars
     * @param string $alt_text
     * @return JsonFlexMessageBuilder
     */
    public static function flex($json, $data_vars, $alt_text = '')
    {
        return new JsonFlexMessageBuilder($json, $data_vars, $alt_text);
    }
}
