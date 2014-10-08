<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 02/10/2014
 * Time: 17:59
 */

namespace AngryDan\HarvestJiraBundle\Sync\Harvest;

use Harvest\HarvestAPI;

class Api extends HarvestAPI {

    /**
     * @var AuthenticationHelper
     */
    protected $authenticationHelper;

    public function setAuthenticationHelper(AuthenticationHelper $authenticationHelper) {
        $this->authenticationHelper = $authenticationHelper;
    }

    public function setupAuthentication() {
        $this->setUser($this->authenticationHelper->getUsername());
        $this->setPassword($this->authenticationHelper->getPassword());
    }

    protected function generateCURL($url)
    {
        $this->setupAuthentication();
        return parent::generateCURL($url);
    }

    protected function generateMultiPartCURL($url, $data)
    {
        $this->setupAuthentication();
        return parent::generateMultiPartCURL($url, $data);
    }
}
