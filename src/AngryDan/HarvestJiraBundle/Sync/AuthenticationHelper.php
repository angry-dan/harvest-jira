<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 02/10/2014
 * Time: 14:03
 */

namespace AngryDan\HarvestJiraBundle\Sync;


use Symfony\Component\HttpFoundation\Session\Session;

abstract class AuthenticationHelper implements AuthenticationHelperInterface
{

    /**
     * @var Session
     */
    protected $session;

    function __construct(Session $session)
    {
        $this->session = $session;
    }

    abstract public function getSessionNamespace();

    public function isLoggedIn()
    {
        return (bool) $this->session->get($this->getSessionNamespace() . 'authString');
    }

    public function login($username, $password)
    {
        $this->session->set(
          $this->getSessionNamespace() . 'authString',
          array(
            'username' => $username,
            'password' => $password,
          )
        );
    }

    public function logout()
    {
        $this->session->remove($this->getSessionNamespace() . 'authString');
    }

    public function getUsername()
    {
        $auth = $this->session->get($this->getSessionNamespace() . 'authString');

        return ($auth) ? $auth['username'] : null;
    }

    public function getPassword()
    {
        $auth = $this->session->get($this->getSessionNamespace() . 'authString');

        return ($auth) ? $auth['password'] : null;
    }
}
