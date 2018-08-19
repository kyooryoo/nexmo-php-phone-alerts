# Broadcast Voice-based Critical Alerts

## Use case
A persistently ringing phone can ensure that critical alerts are delivered to the right persons.
This tutorial does not only sends out voice messages but also see who was contacted, who responded, and when. These voice-based critical alerts are more persistent than a text message, making your message more likely to be noticed. Additionally, with the recipient confirmation, you can be sure that your message made it through.

## Prerequisites
* A [Nexmo account](https://dashboard.nexmo.com/sign-up)
* The [Nexmo CLI](https://github.com/nexmo/nexmo-cli)
* Use [Composer](http://getcomposer.org/) to install the [Nexmo PHP library](https://github.com/nexmo/nexmo-php) and [phpdotenv](https://github.com/vlucas/phpdotenv)
* A publicly accessible web server ([ngrok](https://ngrok.com/))

## Step by Step Guide
0. Publish local 8080 port to the Internet with ngrok, note returned 'ngrok_url'.
```sh
./ngrok http 8080
```
1. Create a Nexmo voice application and note the returned app ID 'nexmo_app'.
```sh
nexmo app:create phone-alerts [ngrok_url]/answer.php [ngrok_url]/event.php --keyfile private.key
```
2. Provision (or reuse) a Nexmo virtual number 'nexmo_number'.
```sh
nexmo number:buy --country_code US --confirm
```
3. Associate the virtual number with the voice application.
```sh
nexmo link:app [nexmo_number] [nexmo_app]
```
4. Get some environment variables ready and secured with phpdotenv.
Copy 'sample.env' to '.env' and fill up the variable values as follows.
```sh
base_path=[ngrok_url]
api_key=[nexmo_api_key]
api_secret=[nexmo_api_secret]
nexmo_app=[nexmo_app]
nexmo_number=[nexmo_number]
test_num_1=[test_number_1]
test_num_2=[test_number_2]
```
4. Create a Nexmo Call Control Object (NCCO) with 'answer.php'.
```sh
<?php
$request = array_merge($_GET, $_POST);
$to = $request['to'];
$from = $request['from'];
$uuid = $request['conversation_uuid'];
$ncco = [
    [   // the default message played with Nexmo Text-To-Speech function
        "action" => "talk",
        "voiceName" => "Jennifer",
        "text" => "Hello, here is your message. I hope you have a nice day."
    ],
    [   // an optional stream section to play pre-record message
        "action" => "stream",
        // adjust the volume level between -1 and 1 in increments of 0.1
        "level" => "-0.4",
        // replace the mp3 sample with anything you wish
        "streamUrl" => ["http://www.noiseaddicts.com/samples_1w72b820/29.mp3"]
    ],
    [   // an optional call confirmation feature starts from here
        "action" => "talk",
        "voiceName" => "Jennifer",
        "text" => "To confirm receipt of this message, please press 1 followed by the pound sign"
    ],
    [
        "action" => "input",
        "submitOnHash" => "true",
        "timeout" => 10
    ],
    [
        "action" => "talk",
        "voiceName" => "Jennifer",
        "text" => "Thank you, you may now hang up."
    ]
];
header('Content-Type: application/json');
echo json_encode($ncco);
```
5. Create 'event.php' with to handle status change of the call.
```sh
<?php
$request = json_decode(file_get_contents('php://input'), true);
if (isset($request['status'])) {
    switch ($request['status']) {
    case 'ringing':
        record_steps("UUID: {$request['conversation_uuid']} - ringing.");
        break;
    case 'answered':
        record_steps("UUID: {$request['conversation_uuid']} - was answered.");
        break;
    // for tracking the calls went to voicemail instead of being answered
    case 'machine':
        record_steps("UUID: {$request['conversation_uuid']} - answering machine.");
        break;
    case 'complete':
        record_steps("To: {$request['to']} - UUID: {$request['conversation_uuid']} - complete.");
        break;
    default:
        break;
    }
}
if (isset($request['dtmf'])) {
  switch ($request['dtmf']) {
      case '1':
          record_steps("UUID: {$request['conversation_uuid']} - confirmed receipt.");
          break;
      default:
          record_steps("UUID: {$request['conversation_uuid']} - other button pressed ({$request['dtmf']}).");
          break;
  }
}
function record_steps($message) {
    file_put_contents('./call_log.txt', $message.PHP_EOL, FILE_APPEND | LOCK_EX);
}
```
6. Create 'broadcast.php' to make outbound alert call.
```sh
<?php
require_once 'vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$basic  = new \Nexmo\Client\Credentials\Basic(getenv('api_key'), getenv('api_secret'));
$keypair = new \Nexmo\Client\Credentials\Keypair(
    file_get_contents(__DIR__ . '/private.key'),
    getenv('nexmo_app')
);
$client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Container($basic, $keypair));
$contacts = [
    "Bob Smith" => getenv('test_num_1'),
    "Jenny Cable" => getenv('test_num_2')
];
foreach ($contacts as $name => $number) {
    $client->calls()->create([
        'to' => [[
            'type' => 'phone',
            'number' => $number
        ]],
        'from' => [
            'type' => 'phone',
            'number' => getenv('nexmo_number')
        ],
        'answer_url' => [getenv('base_path') . '/answer.php'],
        'event_url' => [getenv('base_path') . '/event.php'],
        // keep track of calls went to voicemail
        // replace 'continue' with 'hangup' to stop tracking calls went to voicemail
        'machine_detection' => 'continue'
    ]);
    usleep(5000000)
}
```
7. Run `php broadcast.php` to test the call system.
8. Optional: use a pre-record message instead of Nexmo TTS.
Update the NCCO in 'answer.php' file with a 'stream' action.
9. Optional: track calls went to voicemail instead of being answered.
Add 'machine_detection' in 'broadcast.php' and the case of 'machine' in 'event.php'.
10. Optional: ask the call recipient to confirm the receiving of the call.
Add an extra 'talk' action to give the recipient instruction to follow.
Add `if (isset($request['dtmf']))` after `if (isset($request['status']))` to handle user input.
11. Optional: broadcast the call by:
* Adding TO for tracking number for completed case in 'event.php'.
* Create a dummy contacts list, adding loop for calls and delay in between in 'broadcast.php'
99. Run PHP server with 'php -S 0:8080', create an empty txt file call_log.txt,
and run 'php broadcast.php' to test the result. Good Luck!

## Reference
* The tutorial page https://developer.nexmo.com/tutorials/voice-alerts
* The tutorial code https://github.com/Nexmo/php-voice-alerts-tutorial
* How to use ngrok: https://www.nexmo.com/blog/2017/07/04/local-development-nexmo-ngrok-tunnel-dr/
