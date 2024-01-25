<?php
namespace Auth\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class AuthController extends AbstractActionController
{
    protected $usersTable;
    protected $authManager;

    public function __construct($usersTable, $authManager)
    {
        $this->usersTable = $usersTable;
        $this->authManager = $authManager;
    }

    public function logoutAction()
    {
        $this->authManager->logout();

        return $this->redirect()->toRoute('home');
    }

    public function forbiddenAction()
    {
        $this->getResponse()->setStatusCode(403);
    }
}
