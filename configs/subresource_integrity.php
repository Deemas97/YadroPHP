<?php
return [
    'default_algo' => 'sha384',
    
    'supported_algos' => ['sha256', 'sha384', 'sha512'],
    
    'hashes' => [
        'sha384' => [
            'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css'
            => 'sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65',

            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
            => 'sha384-3B6NwesSXE7YJlcLI9RpRqGf2p/EgVH8BgoKTaUrmKNDkHPStTQ3EyoYjCGXaOTS',

            'https://code.jquery.com/jquery-3.6.0.min.js'
            => 'sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK',

            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js'
            => 'sha384-Uq7tE9OMKGCqc3pyboRZ08az3KDI+KFBFriwJ2Spd/QAHtD5Y1KnlgtexFJUmMRi',

            'https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js'
            => 'sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB',

            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'
            => 'sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p'
        ],
        'sha256' => [],
        'sha512' => [],
    ],
    
    'metadata' => [
        'last_updated' => '2026-03-01 18:00:00',
        'version' => '1.0.0',
        'total_hashes' => 6,
    ],
];