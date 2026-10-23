<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/08/2017
 * Time: 18:20
 */

namespace ADD\Pathology\Event;
use Symfony\Component\EventDispatcher\Event;

class TestItemEvent extends Event
{
    private $testItem;

    /**
     * @return mixed
     */
    public function getTestItem() {
        return $this->testItem;
    }

    function __construct($testItem) {
        $this->testItem = $testItem;
    }

    /**
     * @param mixed $testItem
     */
    public function setTestItem($testItem)
    {
        $this->testItem = $testItem;
    }

}