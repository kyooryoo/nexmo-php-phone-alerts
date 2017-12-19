<?php

require_once 'vendor/autoload.php';

$basic  = new \Nexmo\Client\Credentials\Basic('API_KEY', 'API_SECRET');
$keypair = new \Nexmo\Client\Credentials\Keypair(
    file_get_contents(__DIR__ . '/private.key'),
    '5555f9df-05bb-4a99-9427-6e43c83849b8'
);

$client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Container($basic, $keypair));

$contacts = [
    "Bob Smith" => 14155550200,
    "Jenny Cable" => 14155550355
];

foreach ($contacts as $name => $number) {
    $client->calls()->create([
        'to' => [[
            'type' => 'phone',
            'number' => $number
        ]],
        'from' => [
            'type' => 'phone',
            'number' => '14155550100'
        ],
        'answer_url' => ['https://example.com/answer.php'],
        'event_url' => ['https://example.com/event.php'],
        'machine_detection' => 'continue'
    ]);

    // Sleep for half a second
    usleep(500000);
}
