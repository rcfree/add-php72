<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 15/08/17
 * Time: 11:47
 */

namespace ClinicLE\Search\Output;


class HeadingCollator
{
    private $headings;
    private $patientFields = array();
    private $patientPrefixes = array();
    private $sections;

    function __construct($headings) {
        $this->headings = $headings;
    }
    function getSections() {
        if($this->sections) {
            return $this->sections;
        }
        $sections = array();
        foreach($this->headings as $heading) {

            if(in_array($heading,$this->patientFields)) {
                continue;
            }
            $potSectionBits = explode(".",$heading);
            if(count($potSectionBits)<3) {
                continue;
            }
            $sectionName = $potSectionBits[0].".".$potSectionBits[1];
            $encTypeBits = explode(":",$potSectionBits[2]);
            $encType = $potSectionBits[0].".".$potSectionBits[1].".".$encTypeBits[0];
            if(in_array($sectionName,$sections)) {
                continue;
            }
            if(isset($sections[$sectionName]) && in_array($encType,$sections[$sectionName])) {
                continue;
            }

            $sections[$sectionName][]=$encType;
        }
        $this->sections = $sections;
        return $sections;
    }

    function addPatientPrefix($prefix) {
        $this->patientPrefixes[]=$prefix;
    }
    function getPatientFields() {
        if($this->patientFields) {
            return $this->patientFields;
        }
        foreach($this->headings as $fieldName) {
            foreach($this->patientPrefixes as $ignoredPrefix) {
                if(!preg_match("/^".$ignoredPrefix."/",$fieldName)) {
                    continue;
                }
                $this->patientFields[]=$fieldName;
            }
        }
        return $this->patientFields;
    }
    function getEncTypeFields($encType) {
        if(isset($this->encTypeFields[$encType])) {
            return $this->encTypeFields[$encType];

        }
        $this->encTypeFields[$encType]=preg_grep("/^".$encType."\:/",$this->headings);
        return $this->encTypeFields[$encType];
    }

}