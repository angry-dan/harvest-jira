<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 02/10/2014
 * Time: 13:56
 */

namespace AngryDan\HarvestJiraBundle\Sync;


interface AuthenticationHelperInterface {

    /**
     * @return string
     */
    public function isLoggedIn();

    /**
     * @return bool
     */
    public function login($username, $password);

    /**
     * @return bool
     */
    public function logout();

}
