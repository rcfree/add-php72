<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 15/08/17
 * Time: 12:16
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Common\Util;

class RowProcessor
{
    private $patientIdField = "basic_demog:identifier";
    private $sectionIdField = "section_id";

    private $denormalisedMap = array();

    function __construct(HeadingCollator $headingCollator) {
        $this->headingCollator = $headingCollator;
    }
    function isDifferentPatient($row) {
        $newPatientIdent = $row[$this->patientIdField];
        return($newPatientIdent != $this->lastPatientIdent);
    }
    function getDenormalisedHeadings() {
        $headings = array_keys($this->denormalisedMap["patient"]);
        foreach($this->headingCollator->getSections() as $sectionName => $encTypes) {
            foreach($encTypes as $encType) {
                foreach(range(1,$this->headingCounts[$sectionName]) as $sectionCount) {

                    $encFields = $this->headingCollator->getEncTypeFields($encType);
                    $encFieldsWithCount = array_map(function($field) use($sectionCount) {
                        return $field . "<" . $sectionCount . ">";
                    },$encFields);
                    $headings = array_merge($headings,$encFieldsWithCount);
                }
            }
        }
        return $headings;
    }
    function getDenormalisedRow() {
        $row = array_values($this->denormalisedMap["patient"]);
        foreach($this->headingCollator->getSections() as $sectionName => $encTypes) {
            foreach($this->denormalisedMap[$sectionName] as $sectionId => $encounters) {
            foreach($encounters as $encType => $encData) {
                    $row = array_merge($row,array_values($encData));
                }
            }
        }
        return $row;
    }
    function updateHeadingCounts() {
        foreach($this->headingCollator->getSections() as $sectionName => $encTypes) {
            $sectionCount = count($this->denormalisedMap[$sectionName]);
            if(isset($this->headingCounts[$sectionName]) && $sectionCount<$this->headingCounts[$sectionName]) {
                continue;
            }
            $this->headingCounts[$sectionName]=$sectionCount;
        }
    }
    function processRow($row) {
        $patientIdent = $row[$this->patientIdField];
        $this->lastPatientIdent = $patientIdent;
        if(!isset($this->denormalisedMap["patient"])) {
            $this->denormalisedMap=array("patient" => $this->extractPatientData($row));
        }
        foreach($this->headingCollator->getSections() as $sectionName => $encTypes) {
            foreach($encTypes as $encType) {
                $sectionId = Util::val($encType.":".$this->sectionIdField,$row);
                if(!$sectionId) {
                    continue;
                }
                if($this->isEncounterMapped($patientIdent, $sectionName, $sectionId, $encType)) {
                    continue;
                }
                $encData = $this->extractEncounterData($row,$encType);

                $this->denormalisedMap[$sectionName][$sectionId][$encType]=$encData;

            }
        }
        $this->updateHeadingCounts();
    }
    function isEncounterMapped($patientIdent, $sectionName, $sectionId, $encType) {
        return isset($this->denormalisedMap[$patientIdent][$sectionName]) && isset
    ($this->denormalisedMap[$patientIdent][$sectionName][$sectionId]) && isset
            ($this->denormalisedMap[$patientIdent][$sectionName][$sectionId][$encType]);
    }
    function extractEncounterData($row,$encType) {
        $matchingKeys = $this->headingCollator->getEncTypeFields($encType);
        $filteredData = array_filter($row,function($value, $key) use($matchingKeys) {
            return in_array($key,$matchingKeys);
        }, ARRAY_FILTER_USE_BOTH);
        if(isset($filteredData[$encType.":".$this->sectionIdField])) {
            unset($filteredData[$encType.":".$this->sectionIdField]);
        }
        return $filteredData;
    }
    function extractPatientData($row) {
        $patientFields = $this->headingCollator->getPatientFields();
        $filteredData = array_filter($row,function($value, $key) use($patientFields) {
            return in_array($key,$patientFields);
        }, ARRAY_FILTER_USE_BOTH);
        return $filteredData;
    }
    function getDenormalisedMap() {
        return $this->denormalisedMap;
    }

}