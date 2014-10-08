<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 25/09/2014
 * Time: 19:18
 */

namespace AngryDan\HarvestJiraBundle\Sync\Jira;


use JiraApiBundle\Service\IssueService;

class Issue {

    protected $jira;
    protected $id;
    protected $issue;

    public function __construct(IssueService $jira, $id)
    {
        $this->jira = $jira;
        $this->id = $id;
    }

    public function loadIssue() {
        if ($this->issue === NULL) {
            try {
                $this->issue = $this->jira->get($this->id);
            }
            catch (ClientErrorResponseException $ex) {
                if ($ex->getResponse()->getStatusCode() == 404) {
                    $this->issue = FALSE;
                }
                else {
                    throw $ex;
                }
            }
        }
        return $this;
    }

    public function issueExists() {
        $this->loadIssue();
        return ($this->issue !== FALSE);
    }

    public function getNotes() {
        $this->loadIssue();
        return $this->issue['fields']['summary'];
    }

    public function getTimeLogged($timestamp, $jiraUsername) {
        // TODO - $this->loadWorklogs instead.
        $this->loadIssue();
        $total = 0;
        foreach ($this->issue['fields']['worklog']['worklogs'] as $entry) {
            // start time is an ISO compliant time stamp.
            list($entryTime) = explode('T', $entry['started'], 2);
            if ($entry['author']['name'] == $jiraUsername && $timestamp == $entryTime) {
                $total += $entry['timeSpentSeconds'];
            }
        }
        return $total;
    }
}
