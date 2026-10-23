<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 12:06
 */

namespace ClinicLE\Data\PageFieldSearch;


use ClinicLE\Common\Util;

class SinglePageFieldSearch extends AbstractPageFieldSearch
{
    function findObservations($obsNames) {
        $page = \CLE::sys()->getPageFromPim($this->getPim());


        return $this->getPageObservations($page,null,$obsNames);
    }

    function getMissingObsWarningMessage($missingObs, $page) {
        return "Please enter '".implode("','",$missingObs)."' in this page to ".$this->getCreator()->getMessage();
    }

    function getObservation($obsName, $encounterRecord) {
        return $this->getCreator()->getQueryParams()->get($obsName);
    }

}