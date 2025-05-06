<?php
return [
    'api_key' => env('SMSRU_API_KEY', '8AA72F39-3A7C-9E1A-5CAA-C9B64179FF24'),
    'from' => env('SMSRU_FROM', ''), // можно указать буквенный отправитель
    'test' => env('SMSRU_TEST', false), // true — тестовый режим
];