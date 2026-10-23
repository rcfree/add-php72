<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 12:06
 */

namespace ClinicLE\Data\PageFieldSearch;


use ClinicLE\Common\Util;

class SiblingPageFieldSearch extends AbstractPageFieldSearch
{
    function __construct($pim, $childName, PageFieldSearchCreator $creator)
    {
        $this->childName = $childName;
        parent::__construct($pim, $creator);
    }

    function findObservations($obsNames) {
        $page = \CLE::sys()->getPageFromPim($this->getPim());
        $parent = $page->getParentPage();

        if(!$parent) {
            \ADD::web()->addWarning("Parent of '".$page->getName()."' was not found");
            return;
        }
        $cpage = $parent->getChildPage($this->childName);
        if (!$cpage) {
            $page->addWarning("Child page '$this->childName' was not found");
            return;
        }


        $cer = $cpage->getEncounterRecord($this->getCreator()->getIdValue());
        if(!$cer) {
            \ADD::web()->addWarning("Record for '".$this->childName."' page does not exist");
            return;
        }
        $cer->retrieve();
        return $this->getPageObservations($cpage,$cer,$obsNames);
    }

    function getMissingObsWarningMessage($missingObs, $page) {
        return "Please enter '".implode("','",$missingObs)."' in the '".$page->getSetting('title')."' page to ".$this->getCreator()->getMessage();
    }

    function getObservation($obsName, $encounterRecord) {
        return $encounterRecord->getObsValue($obsName);
    }


}