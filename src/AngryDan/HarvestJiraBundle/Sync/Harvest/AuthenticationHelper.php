<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 01/10/2014
 * Time: 18:06
 */

namespace AngryDan\HarvestJiraBundle\Sync\Harvest;


use AngryDan\HarvestJiraBundle\Sync\AuthenticationHelperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticationHelper extends \AngryDan\HarvestJiraBundle\Sync\AuthenticationHelper {

    const SESSION_PREFIX = 'AngryDan\HarvestJiraBundle\Sync\Harvest\AuthenticationHelper.';

    public function getSessionNamespace() {
        return self::SESSION_PREFIX;
    }
}
