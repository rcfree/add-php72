<?php
namespace ADD\PR;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Output\StandardPagedOutput;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchablePages;
use ClinicLE\Search\Util\JsonFileLoader;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/10/17
 * Time: 21:59
 */
class SearchContent
{
    private $groupSet;
    private $fileName;
    private $searchData;

    /**
     * @return mixed
     */
    public function getSearchData()
    {
        return $this->searchData;
    }

    /**
     * @param mixed $searchData
     */
    public function setSearchData($searchData)
    {
        $this->searchData = $searchData;
    }

    function __construct(GroupSet $groupSet,$fileName) {
        $this->groupSet = $groupSet;
        $this->fileName = $fileName;
    }

    function load() {
        $loader = new JsonFileLoader($this->fileName);
        $this->searchData = $loader->load();
    }
    function runSearch() {
        if(!$this->searchData) {
            throw new \InvalidArgumentException("Search data file '".$this->fileName."' has not been loaded");
        }
        $factory = new DataQueryRunnerFactory($this->groupSet);
        $runner = $factory->createRunner($this->searchData);

        $output = new StandardPagedOutput($runner);
        $output->setPageNo(1);

        return $output->outputResponse();
    }
    function addFilter($groupName, $fieldName,$operator,$values) {
        $groups = $this->searchData['groups'];
        $groupNo = array_search($groupName,array_column($groups, 'name'));
        $filters = $this->searchData['groups'][$groupNo]['filters'];
        $filterNo = array_search($fieldName,array_column($filters,'field'));
        if($filterNo===false) {
            $filter = array("field"=>$fieldName,"operation"=>$operator,"values"=>$values);
            $this->searchData['groups'][$groupNo]['filters'][]=$filter;
            return;
        }
        $this->searchData['groups'][$groupNo]['filters'][$filterNo]["operation"]=$operator;
        $this->searchData['groups'][$groupNo]['filters'][$filterNo]["values"]=$values;
        \CLE::info("searchData",$this->searchData);
    }
        //"pr.prog.referral","referral_date","bt",array($params->get("ref_date_from"),
        //    $params->get("ref_date_to")))
}