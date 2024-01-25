<?php
declare (strict_types = 1);

namespace Admin;

use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'admin' => [
                'type' => Segment::class,
                'priority' => 10,
                'options' => [
                    'route' => '/shorturl/admin[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'changeowner',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'admin' => __DIR__ . '/../view',
        ],
    ],
];
