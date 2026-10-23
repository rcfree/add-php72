<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 12:09
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Page\PageResponse\ContentPageResponse;
use CLE;
use ClinicLE\Search\Query\QueryExport;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Util\DelimitedDataWriter;
use ClinicLE\System\Service\CoreServices;
use RandomLib\Factory;

class SearchExportOutput extends AbstractFileOutput
{
    private $fileName;

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    function outputResponse() {
        $runner = $this->getRunner();
        $runner->setPageNo(null);

        $definitionPath = $this->createDefinitionPath();

        if($runner->hasNoGroups()) {
            touch($definitionPath . "/.complete");

            return array("data"=>array());
        }

        if($runner->hasNoAttributes()) {
            touch($definitionPath . "/.complete");
        }
        $fileName = $this->exportFile($runner,$this->getExportPath());
        CLE::info("create .complete in $definitionPath");
        touch($definitionPath . "/.complete");

        $response = $this->generateFinalResponse($this->getExportPath()."/".$fileName);
        return $response;
    }
    function generateFinalResponse($exportFileName) {
        $content = new \ADD_Content_FileContent();
        $content->setFileName($exportFileName);
        $content->setDownload(true);
        return $content;
    }

    private function exportFile(QueryRunner $runner,$exportPath)
    {
        $fileName = $this->getFileName();
        $writer = new DelimitedDataWriter($exportPath . "/" . $fileName);
        $runnerExport = new QueryExport();
        $runnerExport->runExport($runner, $writer);
        return $fileName;
    }


}