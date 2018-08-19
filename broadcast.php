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
        'machine_detection' => 'continue'
    ]);

    // Sleep for half a second
    usleep(5000000);
}
