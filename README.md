# hurry-bot v1.0
​
Provide the LINE bot framework and basic setting to decrease the time to build a LINE bot.
​
## Installation
 - Fork this project and clone to your environment
 - Open terminal and into the project folder (`cd /path/to/hurry_bot`)
 - Install library by composer (`composer install`)
 - Copy `env.example` to `env` (`cp env.example env`)
 - Generate app key to laravel project (`php artisan key:generate`)
 - Set the permission of storage folder (`chmod -R 777 storage`)
 - Set `LINE_CHANNEL_ACCESS_TOKEN` and `LINE_CHANNEL_SECRET` values to env setting (generated at LINE Developers)
   - LINE Developers > Messaging API > Channel access token
   - LINE Developers > Basic settings > Channel secret
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
## Structures of the framework
 - Services
   - Builder
     - Message
       - Builder Items
         - text
         - image
         - video
         - audio
         - sticker
         - location
         - imagemap
         - flex
       - Methods
         - addBuilder
         - addMultiMessageBuilders
         - getBuilders
     - Flex (construct flex message by json file)
       - JsonFlexMessageBuilder
       - resources/flex
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
   - LineService
     - handleEvent
       - handleMessageEvent
         - handleTextMessageEvent (Main flow, adjustable by user)
         - handleLocationMessageEvent
       - handlePostbackEvent
 - Routes (Fixed)
   - routes/api.php
   - LineController.php
     - webhook
     - pushMessage (not necessary method, for push message by api case)
 - Providers (Fixed)
   - LineServiceProvider.php
 - Configs (Fixed)
   - config/line.php
​
## Illustration
### Examples
 - `app/Services/LineService::replyMessageByText`
​
### How to reply text message
```
$message = new Message();
$message->addBuilder(Message::text($reply_text));
$this->client->replyMessage($message->getBuilders());
```
​
### How to reply multiple messages
  - New `Message` class
```
$message = new Message();
```
  - Set contents by array format, the keys reference by the parameter of message builder
```
// the example of multiple type messages
$contents = [
    ['type' => MessageType::TEXT, 'text' => $example_text],
    ['type' => MessageType::IMAGE, 'url' => $example_image_url],
];
$message->addMultiMessageBuilders($contents);
```
  - reply message by builders 
```
$this->client->replyMessage($message->getBuilders());
```
​
### How to generate flex message and reply
  - Use LINE Bot Designer to design the flex template
  - Copy the part of the bubble container in the property `contents` of flex json
  - Define the variable part of the container by the specific variable format (ex. `${header_text}`)
  - New `Message` class and basic setting
```
$message = new Message();
​
// put the file in the `resource/flex` path
$json = file_get_contents(resource_path("flex/example.json"));
$data_var = [];
$alt_text = 'Example';
```
  - Use the data array of variables to generate a multiple containers of flex message
```
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
```
$this->client->replyMessage($message->getBuilders());
```
