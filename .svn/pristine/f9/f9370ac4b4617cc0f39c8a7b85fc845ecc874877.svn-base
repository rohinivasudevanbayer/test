<?php
declare (strict_types = 1);

namespace Auth;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'logout' => [
                'type' => Literal::class,
                'priority' => 10,
                'options' => [
                    'route' => '/shorturl/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'auth' => [
                'type' => Segment::class,
                'priority' => 10,
                'options' => [
                    'route' => '/shorturl/auth[/:action]',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'forbidden',
                    ],
                ],
            ],
            '403' => [
                'type' => Literal::class,
                'priority' => 10,
                'options' => [
                    'route' => '/shorturl/forbidden',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'forbidden',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'auth' => __DIR__ . '/../view',
        ],
    ],
    'access_filter' => [
        'controllers' => [
            // use following values in allow array key:
            // * for all visitors,
            // @ for logged in users only,
            // ^ for admin and superadmin users only
            \Admin\Controller\AdminController::class => [
                ['actions' => '*', 'allow' => '^'],
            ],
            \Application\Controller\IndexController::class => [
                ['actions' => ['index', 'conditionsOfUse', 'imprint', 'privacyStatement', 'contact'], 'allow' => '*'],
                ['actions' => ['documentation'], 'allow' => '@'],
            ],
            \Application\Controller\UserController::class => [
                ['actions' => ['profile'], 'allow' => '@'],
            ],
            \Shorturl\Controller\RedirectController::class => [
                ['actions' => ['index'], 'allow' => '*'],
            ],
            \Auth\Controller\AuthController::class => [
                ['actions' => ['forbidden'], 'allow' => '*'],
                ['actions' => ['logout'], 'allow' => '@'],
            ],
            \Shorturl\Controller\ShorturlController::class => [
                ['actions' => '*', 'allow' => '@'],
            ],
        ],
    ],

];
