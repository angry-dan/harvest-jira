<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 24/09/2014
 * Time: 18:53
 */

namespace AngryDan\HarvestJiraBundle\Sync\Jira;


use JiraApiBundle\Service\AbstractService;

class WorklogService extends AbstractService {

    const ADJUST_ESTIMATE_NEW = 'new';
    const ADJUST_ESTIMATE_LEAVE = 'leave';
    const ADJUST_ESTIMATE_MANUAL = 'manual';
    const ADJUST_ESTIMATE_AUTO = 'auto';

    const JIRA_TIME_FORMAT = 'Y-m-d\TH:i:s.000O';

    public function get($key) {
        return $this->performQuery(
          $this->createUrl(
            sprintf('issue/%s/worklog', $key)
          )
        );
    }

    public function postWorklog($key, $data) {

        $url = $this->createUrl(sprintf('issue/%s/worklog', $key), array('adjustEstimate' => 'leave'));
        $request = $this->client->post($url, ['Content-Type' => 'Application/json'], json_encode($data));
        $this->response = $request->send();
        return $this->getResponseAsArray();
    }

    /**
     * Get response as an array.
     *
     * @return array
     */
    protected function getResponseAsArray()
    {
        $this->result = $this->response->json();

        if ($this->responseHasErrors()) {
            return false;
        }

        return $this->result;
    }

    /**
     * Indicates whether the response contains errors.
     *
     * @return bool
     */
    protected function responseHasErrors()
    {
        return (
          array_key_exists('errorMessages', $this->result) ||
          array_key_exists('errors', $this->result)
        );
    }
}
