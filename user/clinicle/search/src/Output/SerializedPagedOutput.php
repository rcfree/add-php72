<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 11:58
 */

namespace ClinicLE\Search\Output;

use CLE;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\System\Exception\NotifyUserException;
use ClinicLE\System\Service\CoreServices;

class SerializedPagedOutput extends AbstractFileOutput
{
    private $completeFileCreated = true;
    private $standardOutput;

    function __construct(QueryRunner $runner) {
        parent::__construct($runner);
        $this->standardOutput = new StandardPagedOutput($runner);
    }
    /**
     * @return mixed
     */
    public function getPageNo()
    {
        return $this->standardOutput->getPageNo();
    }

    /**
     * @param mixed $pageNo
     */
    public function setPageNo($pageNo)
    {
        $this->standardOutput->setPageNo($pageNo);
    }

    /**
     * @return bool
     */
    public function isCompleteFileCreated()
    {
        return $this->completeFileCreated;
    }

    /**
     * @param bool $completeFileCreated
     */
    public function setCompleteFileCreated($completeFileCreated)
    {
        $this->completeFileCreated = $completeFileCreated;
    }

    function outputResponse() {
        try {
            $response = $this->standardOutput->outputResponse();
        }
        catch(\Exception $ex) {
            CLE::error("Error during search output:".$ex->getMessage());
            $response = array("error"=>1,"error_text"=>$ex->getMessage(),"data"=>array());
        }
        $definitionPath = $this->createDefinitionPath();
        $log = \CLE::log($this);
        $log->info("Outputting search file to ". $definitionPath);
        file_put_contents($definitionPath."/results",serialize($response));
        if(!$this->isCompleteFileCreated()) {
            return;
        }
        $log->info("Output process complete");
        touch($definitionPath . "/.complete");
    }
}