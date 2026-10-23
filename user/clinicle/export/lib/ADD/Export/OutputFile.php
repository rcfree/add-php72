<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OutputFile
 *
 * @author rcf8
 */
class ADD_Export_OutputFile {

    private $_filterVal;
    private $_filterName;
    private $_format;
    //private $_columns = array('Internal ID' => 1, 'S number'=>1, 'First name' => 1, 'Last name' => 1, 'Date of birth' => 1, 'Record type' => 1, 'Timestamp' => 1);
    private $_columns = array();
    private $_filename;
    private $_lineSep;
    private $_linePrefix;
    private $_lineSuffix;
    private $_filePrefix;
    private $_fileSuffix;
    private $_headSuffix;
    private $_headPrefix;
    private $_headSep;
    private $_emptyField = "";
    private $pseudonymised = false;
    private $includeRecruitment = false;
    private $includeIlhIdentifier = false;

    function __construct($obsFilter, $format = "csv") {
        if ($obsFilter) {
            list($filterName, $filterVal) = explode("|", $obsFilter);

            $this->_filterName = $filterName;
            $this->_filterVal = $filterVal;
        }
        $ofile = tempnam("/tmp", "add-data");
        $this->_fh = fopen($ofile, "w");
        $this->_filename = $ofile;
        $this->_format = $format;


        if ($this->_format == "csv") {
            $this->_headSep = "\",\"";
            $this->_lineSep = "\",\"";
            $this->_linePrefix = "\"";
            $this->_lineSuffix = "\"\n";
            $this->_headPrefix = "\"";
            $this->_headSuffix = "\"\n";
        } else {
            $this->_headPrefix = "<thead><tr><th>";
            $this->_headSuffix = "</th></tr></thead>";
            $this->_headSep = "</th><th>";

            $this->_linePrefix = "<tr><td>";
            $this->_lineSuffix = "</td></tr>\n";
            $this->_lineSep = "</td><td>";

            $this->_filePrefix = "<table class='table table-condensed table-striped'>\n";
            $this->_fileSuffix = "</tbody></table>\n";
            $this->_emptyField = "&nbsp;";
        }
    }

    function setPseudonymised($pseudonymised) {
        $this->pseudonymised = $pseudonymised;
    }

    function setIncludeRecruitment($includeRecruitment) {
        $this->includeRecruitment = $includeRecruitment;
    }

    function setIncludeIlhIdentifier($includeIlhIdentifier) {
        $this->includeIlhIdentifier = $includeIlhIdentifier;
    }

    function getColumns() {
        return array_keys($this->_columns);
    }

    function addObservationRow($patient, $er, $obs, $idList = array()) {
        if (count($obs) == 0)
            return;
        $coreData = array();
        if ($er) {
            $uri = ADD_Util::uri("export/edit", array("pid" => $patient['id'], "eid" => $er->getId()));
            $coreData = array(
                'Record ID' => ($this->_format == "html" ? "<a target=\"_blank\" href=\"$uri\">" : "") . $er->getId() . ($this->_format == "html" ? "</a>" : ""),
                'Parent record ID' => $er->getEncData('parent_encounter_id')
            );
        }
        
        if ($this->pseudonymised || $this->includeIlhIdentifier) {
            $coreData = array_merge($coreData, array("ILH ID" => $patient['ilh_identifier']));
        }
        if ($this->pseudonymised) {
            $coreData = array_merge($coreData, array("Age" => ADD_Field_Util::age($patient['date_of_birth'], ADD_Field_Util::today())));
        } else {
            $coreData = array_merge($coreData, array(
                'S number' => $patient['identifier'],
                'First name' => $patient['first_name'],
                'Last name' => $patient['last_name'],
                'Date of birth' => $patient['date_of_birth'],
				"Age" => ADD_Field_Util::age($patient['date_of_birth'], ADD_Field_Util::today())
            ));
        }
		if ($this->includeRecruitment) {
            $coreData = array_merge($coreData, array(
				'Research DB' => $patient['research_db'],
                'Recruit DB' => $patient['recruit_db'],
                'Recruit status' => $patient['recruit_status']
            ));
        }
        if ($er) {
            $coreData['Timestamp'] = $er->getEncData('date_created');
        }
		$coreData['Gender']=$patient['gender'];
		
        $obs = array_merge($coreData, $obs);
        if ($idList) {
            $obs['Anon ID'] = $idList[$patient['identifier']];
        }
		
        foreach ($obs as $obsName => $obsValue) {
            if (is_array($obsValue))
                $obs[$obsName] = implode(";", $obsValue);
            $this->_columns[$obsName] = 1;
        }
        if (isset($this->_filterName)) {
            if ($obs[$this->_filterName] != $this->_filterVal)
                return;
        }

        $row = array();

        foreach (array_keys($this->_columns) as $col) {
            $row[] = isset($obs[$col]) ? $obs[$col] : $this->_emptyField;
        }
        $line = implode($this->_lineSep, $row);
        fwrite($this->_fh, $this->_linePrefix . $line . $this->_lineSuffix);
    }

    function closeFile() {
        if ($this->_fileSuffix)
            fwrite($this->_fh, $this->_fileSuffix);
        fclose($this->_fh);
        rename($this->getFileName(), $this->getFileName() . "_data");
        file_put_contents($this->getFileName() . "_heads", $this->_filePrefix . $this->_headPrefix . implode($this->_headSep, $this->getColumns()) . $this->_headSuffix);
        $sys = "cat " . $this->getFileName() . "_heads " . $this->getFileName() . "_data > " . $this->getFileName();
        exec($sys, $output);
    }

    function getFileName() {
        return $this->_filename;
    }

}
