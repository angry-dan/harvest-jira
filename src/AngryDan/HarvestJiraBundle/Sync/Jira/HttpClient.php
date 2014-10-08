<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 30/09/2014
 * Time: 14:03
 */

namespace AngryDan\HarvestJiraBundle\Sync\Jira;


use Guzzle\Http\Client;
use Guzzle\Http\Message\RequestInterface;

class HttpClient extends Client {

    /**
     * @var AuthenticationHelper
     */
    protected $authenticationHelper;

    public function setAuthenticationHelper(AuthenticationHelper $authenticationHelper) {
        $this->authenticationHelper = $authenticationHelper;
    }

    public function createRequest($method = RequestInterface::GET, $uri = null, $headers = null, $body = null)
    {
        return parent::createRequest($method, $uri, $headers, $body);
    }

    protected function prepareRequest(RequestInterface $request)
    {
        // Replace configured username/password details with our own from the auth helper.
        if ($options = $this->getConfig(self::CURL_OPTIONS)) {
            unset($options['CURLOPT_USERPWD']);
            $options[CURLOPT_USERPWD] = $this->authenticationHelper->getAuthString();
            $this->getConfig()->set(self::CURL_OPTIONS, $options);
        }
        return parent::prepareRequest($request);
    }
}
