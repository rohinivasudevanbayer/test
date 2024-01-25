<?php
declare (strict_types = 1);

namespace Admin;

use Laminas\Authentication\AuthenticationService;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\AdminController::class => function ($container) {
                    return new Controller\AdminController(
                        $container->get(\Auth\Model\UsersTable::class),
                        $container->get(\Shorturl\Model\DomainsProvider::class),
                        $container->get(\Shorturl\Model\Admins2DomainsTable::class),
                        $container->get(AuthenticationService::class),
                        $container->get(\Shorturl\Model\ShorturlsTable::class),
                        $container->get(\Shorturl\Model\ShorturlsHistoryTable::class),
                        $container->get('config')
                    );
                },
            ],
        ];
    }
}
