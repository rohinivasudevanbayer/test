<?php
namespace Auth\Service;

use Auth\Model\User;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AuthAdapter implements AdapterInterface
{
    /**
     * User email.
     * @var string
     */
    private $email;

    /**
     * Password
     * @var string
     */
    private $password;

    /**
     * User table.
     * @var
     */
    private $userTable;

    /**
     * Constructor.
     */
    public function __construct($userTable)
    {
        $this->userTable = $userTable;
    }

    /**
     * Sets user email.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Sets password.
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
    }

    /**
     * Authenticate via OAuth2 sent headers
     */
    public function oAuthAuthenticate()
    {
        $authenticatedUser = false;
        $headers = apache_request_headers();
        if (!empty($headers['OIDC_CLAIM_email'])) {
            $firstname = $headers['OIDC_CLAIM_given_name'];
            $lastname = $headers['OIDC_CLAIM_family_name'];
            $email = $headers['OIDC_CLAIM_email'];
            $authenticatedUser = $this->userTable
                ->findOrCreateUser($email, $lastname, $firstname);
        }
        if (false === $authenticatedUser && !empty($headers['REDIRECT_OIDC_CLAIM_email'])) {
            $firstname = $headers['REDIRECT_OIDC_CLAIM_given_name'];
            $lastname = $headers['REDIRECT_OIDC_CLAIM_family_name'];
            $email = $headers['REDIRECT_OIDC_CLAIM_email'];
            $authenticatedUser = $this->userTable
                ->findOrCreateUser($email, $lastname, $firstname);
        }
        return $authenticatedUser;
    }

    /**
     * Performs an authentication attempt.
     */
    public function authenticate()
    {
        if (!empty($this->email) && !empty($this->password)) {
            try {
                $authenticatedUser = $this->userTable
                    ->findAuthenticatedUser($this->email, $this->password);
            } catch (\Exception $e) {
                $authenticatedUser = false;
            }
        } else {
            $authenticatedUser = $this->oAuthAuthenticate();
        }

        if ($authenticatedUser) {
            return new Result(
                Result::SUCCESS,
                $authenticatedUser,
                ['Authenticated successfully.']);
        }

        return new Result(
            Result::FAILURE_CREDENTIAL_INVALID,
            null,
            ['Invalid credentials.']);
    }
}
