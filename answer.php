<?php

// The incoming request could be a GET or a POST, depending on how your
// account is configured
$request = array_merge($_GET, $_POST);

// This is the phone number being called
$to = $request['to'];

// This is the caller's phone number
$from = $request['from'];

// Nexmo provide a unique ID for all calls
$uuid = $request['conversation_uuid'];

// For more advanced Conversations you use the above parameters to
// dynamically create the NCCO and provide a personalised experience

$ncco = [
    [
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
    [
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

// Nexmo expect you to return JSON with the correct headers
header('Content-Type: application/json');
echo json_encode($ncco);
