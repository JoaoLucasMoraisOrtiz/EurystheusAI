<?php

return [
    'api_key' => env('GEMINI_API_KEY'),
    'model'   => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    'url'     => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/'),
];
