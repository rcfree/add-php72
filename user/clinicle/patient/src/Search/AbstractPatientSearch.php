<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/05/18
 * Time: 09:24
 */

namespace ClinicLE\Patient\Search;

use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use CLE;

abstract class AbstractPatientSearch
{
    private $patientLookup;
    protected $searchPatients;
    private $permissions;

    function __construct($patientLookup, $searchPatients, $permissions = array())
    {
        $this->patientLookup = $patientLookup;
        $this->searchPatients = $searchPatients;
        $this->permissions = $permissions;
    }

    function getSearchResults($searchParams, $pageNo = 1, $rows = 20)
    {

        $lookupRecords = $this->lookupPatientRecords($searchParams);
        $noLookupResults = count($lookupRecords) == 0;


        $searchResults = $this->generateResultsForPatients($lookupRecords, $pageNo, $rows, $noLookupResults, $searchParams);
        $resultCount = $searchResults['total'];
        $this->addResultNotices($resultCount, $noLookupResults, $this->permissions['create']);

        CLE::db('audit')->auditSearch($searchParams);

        $searchResults['rows_per_page']=$rows;
        $searchResults['page_no']=$pageNo;

        return $searchResults;
    }

    private function lookupPatientRecords($args)
    {
        $records = array();
        if (!$this->patientLookup) {
            CLE::web()->addWarning("No patient lookup provided");
            return array();
        }

        try {
            $records = $this->patientLookup->get_patient_records($args);
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            if (!preg_match("/patient with identifier/", $msg)) {
                if (!preg_match("/is not available/", $msg)) {
                    \CLE::web()->addWarning($this->getMissingSearchEntryWarning());
                } else {
                    CLE::web()->addError($msg);
                }
            } else {
                CLE::web()->addError($msg);
            }
        }
        if (count($records) == 0) {
            return array();
        }
        array_shift($records);
        $records = array_map(function ($x) {
            return $x[0];
        }, $records);
        return $records;
    }

    abstract function getMissingSearchEntryWarning();

    private function getResultsFromPatients($patients)
    {
        $results = array();
        foreach ($patients as $p) {
            $result = array('id' => $p['id']);

            $names = $p['first_name'] . " " . $p['last_name'];
            $result['patient_name'] = $names;
            $result['deleted'] = $p['is_deleted'];

            $result['date_of_birth'] = $p['date_of_birth'];
            $result['nhs_no'] = $p['nhs_no'];
            $result['identifier'] = $p['identifier'];
            $result['address1'] = isset($p['address_line_1']) ? $p['address_line_1'] : "- none -";
            $result['deceased'] = $p['deceased'] == 1;
            $result['added_to_db'] = true;

            if (!$p['is_deleted']) {
                if ($this->permissions['delete']) {
                    $result['deletable'] = true;
                }
            }
            if ($this->permissions['purge']) {
                $result['purgable'] = true;
            }

            $results[] = $result;
        }
        return $results;
    }

    /**
     * @param $records
     * @param $pdb
     * @param $addPerm
     * @param $results
     * @param $pg
     * @param $rows
     */
    private function useLookupRecordsToGetPatients($records, $pg, $rows)
    {
        $results = array();
        $pdb = CLE::db('patient');
        foreach ($records as $record) {
            $patientFromDb = $pdb->getPatientWithIdentifier($record['main_pat_id']);
            $result = array();
            if ($patientFromDb) {
                $temp = $this->getResultsFromPatients(array($patientFromDb));
                $result = $temp[0];
                $result['added_to_db'] = true;
            } else {

                if ($this->permissions['create']) {
                    $result['addable'] = true;

                }

                $result['patient_name'] = $record['first_name'] . " " . $record['last_name'];
                $result['added_to_db'] = false;
                $result['date_of_birth'] = $record['dob'];
                $result['nhs_no'] = $record['nhs_number'];
                $result['identifier'] = $record['main_pat_id'];
                $result['address1'] = $record['pat_addr1'];
                $result['deceased'] = Util::val('death_indicator', $record) == 1;
            }

            $results[] = $result;
        }
        $resultCount = count($results);
        $firstIndex = ($pg * $rows) - $rows + 1;
        $lastIndex = count($results) + $firstIndex -1;

        return array("data"=>$results, "total"=>$resultCount, "first"=>$firstIndex, "last"=>$lastIndex);
    }

    /**
     * @param $lookupRecords
     * @param $pageNo
     * @param $rows
     * @param $noLookupResults
     * @param $terms
     * @return array
     * @internal param $addPerm
     */
    private function generateResultsForPatients($lookupRecords, $pageNo=1, $rows=20, $noLookupResults, $searchParams)
    {
        if ($noLookupResults) {
            $output = $this->findPatientsInDatabase($searchParams, $pageNo, $rows);
            $patients = $output['data'];
            $output['total'] = $output['count'];
            $results = $this->getResultsFromPatients($patients);
            $output['data']=$results;
        } else {
            $output = $this->useLookupRecordsToGetPatients($lookupRecords, $pageNo, $rows);
        }
        return $output;
    }

    abstract function findPatientsInDatabase($searchParams, $pageNo=1, $rows=20);

    /**
     * @param $resultCount
     * @param $noLookupResults
     * @param $addPerm
     */
    private function addResultNotices($resultCount, $noLookupResults)
    {
        if ($resultCount == 0) {
            return;
        }
        //$patText = "<b>" . $resultCount . " " . Util::pluralize($resultCount, "matching patient") . "</b> in ADD";
        //$resultInfo = "$patText" . ($noLookupResults ? "" : " or the hospital's patient database") . "<br/>";
        $resultInfo = "To select a patient click their name in the table below.";
        \CLE::web()->addMessage($resultInfo);

        if ($noLookupResults) {
            return;
        }

        if ($this->permissions['create']) {
            \CLE::web()->addWarning("<b>Starred (*) patients do not currently exist in ADD and will be created automatically if you click their 'Add' button.</b>");
        } else {
            \CLE::web()->addWarning("<b>Starred (*) patients do not currently exist in ADD. You do not have permission to create new patients.</b>");
        }

    }
}