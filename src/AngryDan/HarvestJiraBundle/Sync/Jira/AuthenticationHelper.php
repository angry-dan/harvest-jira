<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 01/10/2014
 * Time: 18:06
 */

namespace AngryDan\HarvestJiraBundle\Sync\Jira;


use AngryDan\HarvestJiraBundle\Sync\AuthenticationHelperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class AuthenticationHelper
 * @package AngryDan\HarvestJiraBundle\Sync\Jira
 */
class AuthenticationHelper extends \AngryDan\HarvestJiraBundle\Sync\AuthenticationHelper {

    const SESSION_PREFIX = 'AngryDan\HarvestJiraBundle\Sync\Jira\AuthenticationHelper.';

    public function getSessionNamespace() {
        return self::SESSION_PREFIX;
    }

    public function getAuthString() {
        return $this->getUsername() . ':' . $this->getPassword();
    }
}
