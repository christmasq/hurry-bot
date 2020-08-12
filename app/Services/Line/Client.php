<?php


namespace App\Services\Line;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use App\Services\Line\Builder\Message;

/**
 * Class Client
 * @package App\Services\Line
 */
class Client
{
    /**
     * @var LINEBot
     */
    protected $bot;

    /**
     * @var string
     */
    protected $reply_token;

    /**
     * ChatService constructor.
     * @param LINEBot $bot
     */
    public function __construct(LINEBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * parse line event request to base event objects
     * @param string $content
     * @param string $signature
     * @return LINEBot\Event\BaseEvent[]|mixed
     * @throws \Exception
     */
    public function parseEvent($content, $signature)
    {
        return $this->bot->parseEventRequest($content, $signature);
    }

    /**
     * set reply token
     * @param string $reply_token
     */
    public function setReplyToken(string $reply_token)
    {
        $this->reply_token = $reply_token;
    }

    /**
     * common method, reply message by builder
     * @param object $builder
     * @param string $reply_token
     * @return bool
     */
    public function replyMessage($builder, $reply_token = '')
    {
        if ($builder) {
            $reply_token = ($reply_token) ?: $this->reply_token;

            $response = $this->bot->replyMessage($reply_token, $builder);

            // record log when failed
            if (!$response->isSucceeded()) {
                Log::debug(__FUNCTION__ . ", " . var_export($response, 1));
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * common method, push message by builder
     * @param string $to user_id/group_id
     * @param object $builder
     */
    public function pushMessage($to, $builder)
    {
        $response = $this->bot->pushMessage($to, $builder);

        // record log when failed
        if (!$response->isSucceeded()) {
            Log::debug(__FUNCTION__ . ", " . var_export($response, 1));
        }
    }

    /**
     * reply image message
     * @param string $text
     */
    public function replyText($text)
    {
        $this->replyMessage(Message::text($text));
    }

    /**
     * reply image message
     * @param string $image_url
     * @param string $preview_image_url
     */
    public function replyImage($image_url, $preview_image_url = '')
    {
        $this->replyMessage(Message::image($image_url, $preview_image_url));
    }

    /**
     * reply video message
     * @param string $video_url
     * @param string $preview_image_url
     */
    public function replyVideo($video_url, $preview_image_url)
    {
        $this->replyMessage(Message::video($video_url, $preview_image_url));
    }

    /**
     * reply sticker message
     * @param int $package_id
     * @param int $sticker_id
     */
    public function replySticker($package_id, $sticker_id)
    {
        $this->replyMessage(Message::sticker($package_id, $sticker_id));
    }

    /**
     * push text message
     * @param string $to
     * @param string $text
     */
    public function pushText($to, $text)
    {
        $this->pushMessage($to, Message::text($text));
    }

    /**
     * push image message
     * @param string $to
     * @param string $image_url
     */
    public function pushImage($to, $image_url)
    {
        $this->pushMessage($to, Message::image($image_url));
    }

    /**
     * get user profile
     * @param string $user_id
     * @return array
     */
    public function getUserProfile($user_id)
    {
        $res = $this->bot->getProfile($user_id);
        return $res->getJSONDecodedBody();
    }

    /**
     * get group member profile
     * @param string $group_id
     * @param string $user_id
     * @return array
     */
    public function getGroupMemberProfile($group_id, $user_id)
    {
        $res = $this->bot->getGroupMemberProfile($group_id, $user_id);
        return $res->getJSONDecodedBody();
    }

    /**
     * leave group
     * @param string $group_id
     * @param bool $is_room
     * @param string $leave_wording
     * @return LINEBot\Response
     */
    public function leaveGroup($group_id, $is_room, $leave_wording = 'Bye.')
    {
        $this->replyText($leave_wording);
        if ($is_room) {
            return $this->bot->leaveRoom($group_id);
        } else {
            return $this->bot->leaveGroup($group_id);
        }
    }
}
