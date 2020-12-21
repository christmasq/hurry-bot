# hurry-bot v1.0

[![pipeline status](https://gitlab.com/chrischuang/hurry-bot/badges/master/pipeline.svg)](https://gitlab.com/chrischuang/hurry-bot/-/commits/master)
[![coverage report](https://gitlab.com/chrischuang/hurry-bot/badges/master/coverage.svg)](https://gitlab.com/chrischuang/hurry-bot/-/commits/master)

## Introduction
Provide the LINE bot framework and basic setting to decrease the time to build a LINE bot.

https://gitlab.com/chrischuang/hurry-bot

## Documentation
 - LINE Messaging API SDK for PHP: [line-bot-sdk-php](https://github.com/line/line-bot-sdk-php)
 - LINE Develops
   - Console: [Console](https://developers.line.biz/console/)
   - Reference: [Messaging API reference](https://developers.line.biz/en/reference/messaging-api/)

## Requirements
 - PHP 7.2.5 or later
 - [Composer](https://getcomposer.org/download/)
 - [Gitlab](https://gitlab.com/) account

## Installation
 - Fork this project and clone to your environment
```bash
git clone git@gitlab.com:{username}/hurry-bot.git
```
 - Open terminal and into the project folder
```bash
cd /path/to/hurry_bot
```
 - Install library by composer
```bash
composer install
```
 - Copy `env.example` to `env`
```bash
cp env.example env
```
 - Generate app key to laravel project
```bash
php artisan key:generate
``` 
 - Set the permission of storage folder
```bash
chmod -R 777 storage
```
 - Set `LINE_CHANNEL_ACCESS_TOKEN` and `LINE_CHANNEL_SECRET` values to env setting (generated at LINE Developers)
   - LINE Developers > Messaging API > Channel access token
     - `https://developers.line.biz/console/channel/{channel_id}/messaging-api`
   - LINE Developers > Basic settings > Channel secret
     - `https://developers.line.biz/console/channel/{channel_id}/basics`
 - Set webhook settings at LINE Developers
   - LINE Developers > Messaging API > Webhook settings
   - Expected webhook url: `https://yourdomain/api/line/webhook` (`yourdomain` is substitute word)
 - (Optional) Set `APP_URL` value of env by your domain (for illustrating the examples)
 - (Optional) Testing by the example command
   - Example commands
     - show command
     - show text
     - show image
     - show sticker
     - show location
     - show video
     - show audio
     - show multiple message
     - show flex
     - show flex2
 - Adjust `handleEvent` ref methods of LineService class for user features
 - Run and check
 - Done
​
## Components
 ### Services
   - Builder
     - Message
       - Items: [LINE Message types](https://developers.line.biz/en/docs/messaging-api/message-types/)
         - text, image, video, audio, sticker, location, imagemap, flex
       - Methods
         - addBuilder
         - addMultiMessageBuilders
         - getBuilders
     - Flex (construct flex message by json file)
       - JsonFlexMessageBuilder
       - resources/flex

   ---
 
   - Client
      - Flow Methods
        - parseEvent (parse LINE event request to base event objects)
        - setReplyToken (set reply token for reply message)
      - Chat Methods
        - replyMessage (reply message by message builders)
          - replyText
          - replyImage
          - replyVideo
          - replySticker
        - pushMessage (push message to specific target by message builders)
          - pushText
          - pushImage
      - GetInfo Methods
        - getUserProfile
        - getGroupMemberProfile
      - Action Methods
        - leaveGroup (let LINE bot leave the current group or room)

   ---

   - LineService
     - handleEvent
       - handleMessageEvent
         - handleTextMessageEvent (Main flow, adjustable by user)
         - handleLocationMessageEvent
       - handlePostbackEvent
 
   ---

 ### Routes (Fixed)
   - routes/api.php
   - LineController.php
     - webhook
     - pushMessage (not necessary method, for push message by api case)
 ### Providers (Fixed)
   - LineServiceProvider.php

 ### Configs (Fixed)
   - config/line.php
​
## Illustration
### Examples
 - `app/Services/LineService::replyMessageByText`
​
### How to reply text message
```php
$message = new Message();
$message->addBuilder(Message::text($reply_text));
$this->client->replyMessage($message->getBuilders());
```
​
### How to reply multiple messages
  - New `Message` class
```php
$message = new Message();
```
  - Set contents by array format, the keys reference by the parameter of message builder
```php
// the example of multiple type messages
$contents = [
    ['type' => MessageType::TEXT, 'text' => $example_text],
    ['type' => MessageType::IMAGE, 'url' => $example_image_url],
];
$message->addMultiMessageBuilders($contents);
```
  - reply message by builders 
```php
$this->client->replyMessage($message->getBuilders());
```
​
### How to generate flex message and reply
  - Use LINE Bot Designer to design the flex template
  - Copy the part of the bubble container in the property `contents` of flex json
  - Define the variable part of the container by the specific variable format (ex. `${header_text}`)
  - New `Message` class and basic setting
```php
$message = new Message();
​
// put the file in the `resource/flex` path
$json = file_get_contents(resource_path("flex/example.json"));
$data_var = [];
$alt_text = 'Example';
```
  - Use the data array of variables to generate a multiple containers of flex message
```php
// example of five bubble containers in carousel container
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
```
  - reply message by builder
```php
$this->client->replyMessage($message->getBuilders());
```
