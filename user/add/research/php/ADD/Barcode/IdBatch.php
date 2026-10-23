<?php
namespace ADD\Research\Barcode;
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 05/07/2019
 * Time: 10:26
 */
class IdBatch {
    public $ids;
    function __construct($ids) {
        $this->ids = $ids;
        $this->currBatch = 0;
    }
    function nextBatch($numberPerBatch) {
        $batch = array_slice($this->ids, $this->currBatch * $numberPerBatch, $numberPerBatch);
        if (count($batch)==0) {
            return false;
        }
        $this->currBatch++;
        return $batch;
    }

    function reset() {
        $this->currBatch = 0;
    }

}