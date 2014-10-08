<?php

namespace AngryDan\HarvestJiraBundle\Controller;

use AngryDan\HarvestJiraBundle\Sync\Harvest;
use AngryDan\HarvestJiraBundle\Sync\Jira;
use AngryDan\HarvestJiraBundle\Sync\Syncer;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Harvest\HarvestAPI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;

class HomeController extends Controller
{

    /**
     * trim whitespace, get the issue number, trim separators (space, colon or dash optionally surrounded with
     * space) and then the notes.
     *
     */
    const HARVEST_COMMENT_REGEX = '/^\s*([A-Z]+-[0-9]+)\s*[\s-:]\s*(.*)/';

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function loginAction(Request $request) {

        $form = $this->createFormBuilder()
            ->add('harvestUsername', 'text')
            ->add('havestPassword', 'password')

            ->add('jiraUsername', 'text')
            ->add('jiraPassword', 'password')

            ->add('login', 'submit', array('label' => 'Login'))
            ->getForm();

        $form->handleRequest($request);

        /** @var Harvest\AuthenticationHelper $harvestAuth */
        $harvestAuth = $this->get('harvest_jira.harvest.authentication_helper');

        /** @var Jira\AuthenticationHelper $jiraAuth */
        $jiraAuth = $this->get('harvest_jira.jira.authentication_helper');

        if ($form->isValid()) {
            $harvestAuth->login($form->get('harvestUsername')->getData(), $form->get('havestPassword')->getData());
            $jiraAuth->login($form->get('jiraUsername')->getData(), $form->get('jiraPassword')->getData());
        }

        if ($jiraAuth->isLoggedIn() && $harvestAuth->isLoggedIn()) {
            return new RedirectResponse($this->generateUrl('_home'));
        }


        return $this->render('HarvestJiraBundle:Home:login.html.twig', array('form' => $form->createView()));

        // Provide a form to allow login to Harvest and JIRA.
        // Ensure a method of testing the authentication.
        // Store the authentication.
        // Use a compiler pass on the controller to override the jira_api.rest_client/curl.options/CURLOPT_USERPWD
        // I mean, use a compiler pass so that our own client get's used. Our client will require access to the session
        // storage as a depenx  dency!

    }

    public function logoutAction() {
        $this->get('harvest_jira.jira.authentication_helper')->logout();
        $this->get('harvest_jira.harvest.authentication_helper')->logout();
    }

    public function indexAction($timestamp = NULL) {

        /** @var Harvest\AuthenticationHelper $harvestAuth */
        $harvestAuth = $this->get('harvest_jira.harvest.authentication_helper');

        /** @var Jira\AuthenticationHelper $jiraAuth */
        $jiraAuth = $this->get('harvest_jira.jira.authentication_helper');

        if (!$jiraAuth->isLoggedIn() || !$harvestAuth->isLoggedIn()) {
            return new RedirectResponse($this->generateUrl('_login'));
        }

        if (!$timestamp) {
            $timestamp = date('Y-m-d');
        }
        $timestamp_unix = strtotime($timestamp);

        /**
         * Internal conventions:
         *  Dates as 2014-12-31
         *  Log times as ints in seconds.
         */

        $variables = array(
            'entries' => array(),
            'timestamp' => $timestamp,
            'yesterday' => date('Y-m-d', strtotime('yesterday', $timestamp_unix)),
            'tomorrow' => date('Y-m-d', strtotime('tomorrow', $timestamp_unix)),
        );


        $harvestTotal = 0;
        $jiraTotal = 0;

        /** @var Jira $jira */
        $jira = $this->get('harvest_jira.jira');

        /** @var Harvest $harvest */
        $harvest = $this->get('harvest_jira.harvest');

        $stopwatch = $this->get('debug.stopwatch');

        $stopwatch->start('harvestGetEntriesGroupedById');
        $allEntries = $harvest->getEntriesGroupedById($timestamp);
        $stopwatch->stop('harvestGetEntriesGroupedById');

        foreach ($allEntries as $id => $entries) {
            $harvestTime = $harvest->getTotalTimeLogged($entries);
            $harvestTotal += $harvestTime;

            $viewRow = array();
            $viewRow['id'] = $id;
            $viewRow['logs'] = [];
            $viewRow['timeSpent'] = date('G:i', $harvestTime);
            $viewRow['id_exists'] = FALSE;

            if ($id) {
                $stopwatch->start('jiraGetIssue');
                try {
                    $issue = $jira->getIssue($id)->loadIssue();
                }
                catch (ClientErrorResponseException $ex) {
                    if ($ex->getResponse()->getStatusCode() == 401) {
                        $jiraAuth->logout();
                        return new RedirectResponse($this->generateUrl('_login'));
                    }
                    throw $ex;
                }
                $stopwatch->stop('jiraGetIssue');

                if ($issue->issueExists()) {
                    $jiraTime = $issue->getTimeLogged($timestamp, $jiraAuth->getUsername());
                    $jiraTotal += $jiraTime;
                    $viewRow['id_exists'] = TRUE;
                    $viewRow['notes'] = $issue->getNotes();
                    $viewRow['jiraTime'] = date('G:i', $jiraTime);
                    $viewRow['unloggedTime'] = FALSE;
                    // Avoid showing 0:00 in case of rounding errors.
                    if (($harvestTime - $jiraTime) > 60) {
                        $viewRow['unloggedTime'] = date('G:i', $harvestTime - $jiraTime);
                    }
                }
            }

            /** @var Harvest\Entry $entry */
            foreach ($entries as $entry) {
                $viewRow['logs'][] = array(
                    'notes' => $entry->getNotes(),
                    'timeSpent' => date('G:i', $entry->getTimeLogged()),
                    'client' => $entry->get('client'),
                    'project' => $entry->get('project'),
                );
            }
            $variables['entries'][] = $viewRow;
        }


        $variables['jiraTotal'] =  date('G:i', $jiraTotal);
        $variables['harvestTotal'] =  date('G:i', $harvestTotal);
        $variables['percentInSync'] = ($jiraTotal && $harvestTotal) ? round(($jiraTotal / $harvestTotal) * 100) . '%' : '0%';


        return $this->render('HarvestJiraBundle:Home:index.html.twig', $variables);
    }

    public function syncAction($id, $timestamp) {

        if (!$timestamp) {
            $timestamp = date('Y-m-d');
        }

        /** @var Jira $jira */
        $jira = $this->get('harvest_jira.jira');

        /** @var Harvest $harvest */
        $harvest = $this->get('harvest_jira.harvest');

        $entries = $harvest->getEntriesById($timestamp, $id);
        $harvestTime = $harvest->getTotalTimeLogged($entries);

        $issue = $jira->getIssue($id);
        $jiraTime = $issue->getTimeLogged($timestamp, $this->get('harvest_jira.jira.authentication_helper')->getUsername());


        $remainingTime = $harvestTime - $jiraTime;

        $jira->logTime($timestamp, $id, $remainingTime, 'Syncing time from Harvest');

        return new RedirectResponse($this->generateUrl('_home', ['timestamp' => $timestamp]));
    }

}
