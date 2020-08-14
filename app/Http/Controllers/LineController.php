<?php

namespace App\Http\Controllers;

use App\Services\Line\Builder\Message;
use App\Services\Line\ChatService;
use App\Services\Line\Client;
use App\Services\LineService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class LineController
 * @package App\Http\Controllers
 */
class LineController extends Controller
{
    /**
     * handle event by webhook
     * @param Request $request
     * @param Client $client
     * @param LineService $line_service
     */
    public function webhook(Request $request, Client $client, LineService $line_service)
    {
        try {
            $events = $client->parseEvent($request->getContent(), $request->server('HTTP_X_LINE_SIGNATURE'));
            $line_service->handleEvent($events);
        } catch (Exception $e) {
            Log::error($e->getMessage() . "\nRequest Header: " . $request->headers, $request->toArray());
        }
    }

    /**
     * push message by send api request
     * input:
     *  - to: user_id / group_id / room_id
     *  - contents: message contents by json format
     *    (ex. [{"type":"text", "text":"Hi"},{"type":"text", "text":"This is bot."}])
     * @param Request $request
     * @param Client $client
     */
    public function pushMessage(Request $request, Client $client)
    {
        try {
            $to = $request->input('to'); // user_id or group_id
            $contents = $request->input('contents'); // json format
            $content_array = json_decode($contents, true);
            if ($content_array) {
                $message = new Message();
                $message->addMultiMessageBuilders($content_array);
                $client->pushMessage($to, $message->getBuilders());
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage() . "\nRequest Header: " . $request->headers, $request->toArray());
        }
    }
}
