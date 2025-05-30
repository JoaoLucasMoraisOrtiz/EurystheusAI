<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = new Client();
$apiKey = $_ENV['GEMINI_API_KEY'];

echo "Testing Gemini API with key: " . substr($apiKey, 0, 10) . "..." . PHP_EOL;

try {
    $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
        'json' => [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Hello, can you respond with just "OK" to test the connection?']
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.5,
                'maxOutputTokens' => 100,
            ]
        ]
    ]);

    echo "Status Code: " . $response->getStatusCode() . PHP_EOL;
    echo "Response Body: " . $response->getBody()->getContents() . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        echo "Response Body: " . $e->getResponse()->getBody()->getContents() . PHP_EOL;
    }
}
