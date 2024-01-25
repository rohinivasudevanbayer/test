<?php
declare (strict_types = 1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Auth\Service\AuthManager;
use Shorturl\Model\ShorturlsTable;

class UserController extends AbstractActionController
{
    protected $config;

    public function __construct($config, AuthManager $authManager, ShorturlsTable $shorturlsTable)
    {
        $this->config = $config;
        $this->authManager = $authManager;
        $this->shorturlsTable = $shorturlsTable;
    }

    public function profileAction()
    {
        $user = $this->authManager->getUser();
        $userId = $user->id;
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $queryParams = $this->getRequest()->getQuery();
            if (!empty($queryParams['id'])) {
                $userId = $queryParams['id'];
            }
        }

        $allShortUrls = $this->shorturlsTable->countOwnShortUrls($userId);
        $sharedShortUrls = $this->shorturlsTable->countSharedShortUrls($userId);
        $inactiveUrls  = $this->shorturlsTable->countInactiveShortUrls($userId);
        $myPopularUrls = $this->shorturlsTable->getTopShortUrls($userId);

        return new ViewModel([
            'allShortUrls' => $allShortUrls,
            'sharedUrls' => $sharedShortUrls,
            'inactiveUrls' => $inactiveUrls,
            'myPopularUrls' => $myPopularUrls,
        ]);
    }
}
