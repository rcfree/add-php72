<?php
namespace ClinicLE\Client\Data;

use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\Data\Query\QueryRunner;
use CLE;
use ADD_Database_Encounter as EncounterDatabase;
use ClinicLE\Common\Util;
use ClinicLE\DataFrame\DataFrame;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 12:19
 */
abstract class AbstractEncounterExtractor
{
    private $query;
    private $queryRunner;
    private $encounterDb;

    /**
     * @return EncounterDatabase
     */
    public function getEncounterDb()
    {
        return $this->encounterDb;
    }

    /**
     * @param EncounterDatabase $encounterDb
     */
    public function setEncounterDb($encounterDb)
    {
        $this->encounterDb = $encounterDb;
    }

    /**
     * @return QueryRunner
     */
    public function getQueryRunner()
    {
        return $this->queryRunner;
    }

    /**
     * @param QueryRunner $queryRunner
     */
    public function setQueryRunner($queryRunner)
    {
        $this->queryRunner = $queryRunner;
    } /** @var  $runner \ClinicLE\Data\Query\QueryRunner */

    /**
     * @return AbstractEncounterQuery
     */
    public function getQuery()
    {
        return $this->query;
    }


    function __construct(AbstractEncounterQuery $query, QueryRunner $queryRunner, EncounterDatabase $encounterDb)
    {
        $this->query = $query;
        $this->queryRunner = $queryRunner;
        $this->encounterDb = $encounterDb;
    }

    function extractData(EncounterExtractorParams $params, DataFrame $dataFrame)
    {
        $this->populateQuery($params);
        return $this->outputDataFromQuery($params, $dataFrame);
    }

    function outputDataFromQuery(EncounterExtractorParams $params, DataFrame $dataFrame)
    {
        $data = array();

        $rowsPerPage = $params->getRowsPerPage();
        $pageNo = $params->getPageNo();

        $query = $this->getQuery();
        if ($rowsPerPage) {
            $start = ($pageNo * $rowsPerPage) - $rowsPerPage;
            $query->limitRows($start, $rowsPerPage);
        }

        $runner = $this->queryRunner;

        $encs = $runner->getRows($query);
        $countEncs = $runner->countRows($query);

        foreach ($encs as $enc) {
            //$row = $enc;
            $row = array();
            foreach ($enc as $name => $value) {
                $row[$name] = $this->determineFieldOutput($name, $value);
            }

            $dataFrame->addRow($row);
        }
        $data['data'] = $dataFrame->toArray();
        $data['total'] = $countEncs + 0;

        if (!$rowsPerPage) {
            return $data;
        }

        $firstRow = ($pageNo * $rowsPerPage) - $rowsPerPage;

        $data['first'] = $firstRow + 1;
        $data['last'] = $firstRow + count($dataFrame->getRows());
        $data['rows_per_page'] = $rowsPerPage;
        $data['page_no'] = $pageNo;
        if ($query->isDisabled()) {
            $data['disabled'] = 1;
        }
        return $data;
    }

    abstract function determineFieldOutput($obsName, $obsValue);

    /**
     * @param EncounterExtractorParams $params
     */
    abstract function populateQuery(EncounterExtractorParams $params);

}