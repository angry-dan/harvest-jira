<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 25/09/2014
 * Time: 19:09
 */

namespace AngryDan\HarvestJiraBundle\Sync;


use AngryDan\HarvestJiraBundle\Sync\Jira\Issue;
use AngryDan\HarvestJiraBundle\Sync\Jira\WorklogService;
use JiraApiBundle\Service\IssueService;

class Jira {

    protected $jira;
    /**
     * @var WorklogService
     */
    protected $worklog;

    public function __construct(IssueService $jira)
    {
        $this->jira = $jira;
    }

    /**
     * @param WorklogService $worklog
     */
    public function setWorklog(WorklogService $worklog)
    {
        $this->worklog = $worklog;
    }



    /**
     * @param $id
     *
     * @return Issue
     */
    public function getIssue($id) {
        return new Issue($this->jira, $id);
    }

    public function logTime($timestamp, $id, $duration, $comment) {
        $this->worklog->postWorklog($id, array(
            'timeSpent' => $this->makeJiraTimeString($duration),
            'started' => date(WorklogService::JIRA_TIME_FORMAT, strtotime($timestamp)),
            'comment' => $comment,
          ));
    }

    /**
     * Converts our internal representation of time (seconds) into a JIRA time string.
     */
    public function makeJiraTimeString($time) {
        return ($time / 60) . "m";
    }
}



