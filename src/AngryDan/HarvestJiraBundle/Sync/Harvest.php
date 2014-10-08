<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 25/09/2014
 * Time: 19:09
 */

namespace AngryDan\HarvestJiraBundle\Sync;


use AngryDan\HarvestJiraBundle\Sync\Harvest\Entry;
use Harvest\HarvestAPI;

class Harvest {

    protected $harvest;

    public function __construct(HarvestAPI $harvest) {
        $this->harvest = $harvest;
    }

    /**
     * @return Entry[]
     */
    public function getEntries($timestamp) {

        $timestamp = strtotime($timestamp);

        $dayOfYear = date('z', $timestamp) + 1;
        $year = date('Y', $timestamp);
        $harvestEntries = $this->harvest->getDailyActivity($dayOfYear, $year)->data->dayEntries;
        $entries = array();
        foreach ($harvestEntries as $entry) {
            $entries[] = new Entry($entry);
        }
        return $entries;
    }

    /**
     * @return array()
     */
    public function getEntriesGroupedById($timestamp) {
        $entries = $this->getEntries($timestamp);
        $return = [];
        foreach ($entries as $entry) {
            $return[$entry->getId()][] = $entry;
        }
        return $return;
    }

    public function getEntriesById($timestamp, $id) {
        // TODO make this more efficient.
        $entries = $this->getEntriesGroupedById($timestamp);
        return (isset($entries[$id])) ? $entries[$id] : array();
    }

    public static function getTotalTimeLogged(array $entries) {
        $time = 0;
        /** @var Entry $entry */
        foreach ($entries as $entry) {
            $time += $entry->getTimeLogged();
        }
        return $time;
    }
}
