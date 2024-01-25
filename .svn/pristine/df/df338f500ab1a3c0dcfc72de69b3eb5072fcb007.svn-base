<?php
namespace Auth\Service;

use Auth\Model\User;
use \Laminas\Authentication\AuthenticationService;
use \Laminas\Session\SessionManager;

class AuthManager
{
    /**
     * Authentication service.
     * @var \Laminas\Authentication\AuthenticationService
     */
    private $authService;

    /**
     * Session manager.
     * @var \Laminas\Session\SessionManager
     */
    private $sessionManager;

    /**
     * Contents of the 'access_filter' config key.
     * @var array
     */
    private $config;

    public function __construct(AuthenticationService $authService, SessionManager $sessionManager, array $config)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    /**
     * Check if the user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn(): bool
    {
        return $this->authService->hasIdentity();
    }

    /**
     * Get the current user
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->authService->getIdentity();
    }

    /**
     * Login the oAuth user
     *
     * @return \Laminas\Authentication\Result
     */
    public function autoOAuthLogin(): \Laminas\Authentication\Result
    {
        $result = $this->authService->authenticate();
        return $result;
    }

    /**
     * Logout the current user
     *
     * @return void
     */
    public function logout()
    {
        if (!$this->isLoggedIn()) {
            throw new \Exception('The user is not logged in');
        }

        $this->authService->clearIdentity();
    }

    /**
     * Check if the current user is allowed to access the given controller/action
     *
     * @param string $controllerName
     * @param string $actionName
     * @return boolean
     */
    public function filterAccess(string $controllerName, string $actionName): bool
    {
        if (isset($this->config['controllers'][$controllerName])) {
            $items = $this->config['controllers'][$controllerName];
            foreach ($items as $item) {
                $actionList = $item['actions'];
                $allow = $item['allow'];
                if ('*' === $actionList || (is_array($actionList) && in_array($actionName, $actionList))) {
                    if ('*' === $allow) {
                        return true;
                    } else if ('@' === $allow && $this->isLoggedIn()) {
                        return true;
                    } else if ('^' === $allow && $this->isLoggedIn() && ($this->authService->getIdentity()->isSuperAdmin() || $this->authService->getIdentity()->isAdmin())) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }
}
