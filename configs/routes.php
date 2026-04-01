<?php
const ROUTES_CONFIG = [
    // HTML
    [
        'path' => '/',
        'http_method' => 'GET',
        'controller' => 'App\Controller\Web\MainController',
        'controller_method' => 'index'
    ],
    [
        'path' => '/about',
        'http_method' => 'GET',
        'controller' => 'App\Controller\Web\AboutController',
        'controller_method' => 'index'
    ],
    [
        'path' => '/news',
        'http_method' => 'GET',
        'controller' => 'App\Controller\Web\NewsController',
        'controller_method' => 'index'
    ],
    [
        'path' => '/news/{id}',
        'http_method' => 'GET',
        'controller' => 'App\Controller\Web\NewsController',
        'controller_method' => 'show'
    ],
    
    // JSON
    [
        'path' => '/api/admin/logout',
        'http_method' => 'POST',
        'is_xhr' => true,
        'controller' => 'App\Controller\Admin\Api\AuthApiController',
        'controller_method' => 'logout'
    ],
    [
        'path' => '/api/admin/profile/edit',
        'http_method' => 'POST',
        'is_xhr' => true,
        'controller' => 'App\Controller\Admin\Api\ProfileApiController',
        'controller_method' => 'update'
    ],
    [
        'path' => '/api/admin/profile/change_password',
        'http_method' => 'POST',
        'is_xhr' => true,
        'controller' => 'App\Controller\Admin\Api\ProfileApiController',
        'controller_method' => 'changePassword'
    ],
];