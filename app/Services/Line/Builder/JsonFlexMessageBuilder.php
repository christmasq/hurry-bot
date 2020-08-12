<?php
namespace App\Services\Line\Builder;

use LINE\LINEBot\MessageBuilder\RawMessageBuilder;

/**
 * Class JsonFlexMessageBuilder
 * @package App\Services\LineBot\FlexMessage
 */
class JsonFlexMessageBuilder extends RawMessageBuilder
{
    const NEW_MESSAGE_WORDING = 'New Message';

    /**
     * JsonFlexMessageBuilder constructor.
     * @param $alt_text
     * @param $json
     * @param $data_vars
     */
    public function __construct($json, $data_vars, $alt_text = '')
    {
        $alt_text = empty($alt_text) ? static::NEW_MESSAGE_WORDING : $alt_text;

        // init message
        $message = [
            'type' => 'flex',
            'altText' => $alt_text,
            'contents' => [
                'type' => 'carousel',
                'contents' => [],
            ],
        ];

        foreach ($data_vars as $vars) {
            $message['contents']['contents'][] = json_decode($this->parse($json, $vars), 1);
        }

        parent::__construct($message);
    }

    /**
     * @param $json
     * @param array $variables
     * @return string|string[]|null
     */
    public function parse($json, array $variables)
    {
        $parsed_json = $json;
        foreach ($variables as $search => $replace) {
            $pattern = '/\${' . $search . '}/';
            $parsed_json = preg_replace($pattern, $replace, $parsed_json);
        }

        return $parsed_json;
    }
}
