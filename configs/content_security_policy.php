<?php
return [
    'production' => [
        'default-src' => ["'self'"],

        'script-src' => [
            "'self'", "'unsafe-inline'", 'https:', 'http:',
            'https://mc.yandex.ru',
            'https://yastatic.net',
            'https://yandex.ru',
            'https://web-sdk.s3.yandex.net'
        ],

        'style-src' => ["'self'", "'unsafe-inline'", 'https:', 'http:'],
        'img-src' => ["'self'", 'data:', 'blob:', 'https:', 'http:'],
        'font-src' => ["'self'", 'data:', 'https:', 'http:'],
        'connect-src' => ["'self'", 'https:', 'http:', 'ws:', 'wss:'],
        'worker-src' => ["'self'", 'blob:'],

        'frame-src' => [
            "'self'",
            'https://yandex.ru',
            'https://vk.com',
            'https://calendar.yandex.ru'
        ],
        
        'frame-ancestors' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'object-src' => ["'none'"]
    ],
    
    'dev' => [
        'default-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https:', 'http:'],
        'style-src' => ["'self'", "'unsafe-inline'", 'https:', 'http:'],
        'img-src' => ["'self'", 'data:', 'blob:', 'https:', 'http:'],
        'font-src' => ["'self'", 'data:', 'https:', 'http:'],
        'connect-src' => ["'self'", 'https:', 'http:', 'ws:', 'wss:'],
        'worker-src' => ["'self'", 'blob:'],
        'frame-src' => ["'self'", 'https:', 'http:'],
        'frame-ancestors' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'object-src' => ["'none'"]
    ],
    
    'test' => [
        'default-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https:', 'http:'],
        'style-src' => ["'self'", "'unsafe-inline'", 'https:', 'http:'],
        'img-src' => ["'self'", 'data:', 'blob:', 'https:', 'http:'],
        'font-src' => ["'self'", 'data:', 'https:', 'http:'],
        'connect-src' => ["'self'", 'https:', 'http:'],
        'frame-ancestors' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'object-src' => ["'none'"]
    ]
];