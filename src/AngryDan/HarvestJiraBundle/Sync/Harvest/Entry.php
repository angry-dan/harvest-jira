<?php
/**
 * Created by PhpStorm.
 * User: danj
 * Date: 25/09/2014
 * Time: 19:16
 */

namespace AngryDan\HarvestJiraBundle\Sync\Harvest;


use Harvest\Model\DayEntry;

class Entry {

    /**
     * trim whitespace, get the issue number, trim separators (space, colon or dash optionally surrounded with
     * space) and then the notes.
     *
     */
    const HARVEST_COMMENT_REGEX = '/^\s*([A-Z]+-[0-9]+)\s*[\s-:]\s*(.*)/';

    protected $entry;
    protected $notes;
    protected $id;

    function __construct(DayEntry $entry)
    {
        $this->entry = $entry;
        $this->notes = $entry->notes;
        if (preg_match(self::HARVEST_COMMENT_REGEX, $this->notes, $matches)) {
            list(,$this->id, $this->notes) = $matches;
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Get the time that was logged, in seconds.
     *
     * @return int
     */
    public function getTimeLogged() {
        return (int)($this->entry->get('hours-without-timer') * 60 * 60);
    }

    public function get($property) {
        return $this->entry->get($property);
    }
}


