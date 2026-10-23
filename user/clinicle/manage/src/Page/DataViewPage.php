<?php

namespace ClinicLE\Manage\Page;

use ClinicLE\Command\CommandRunner;
use CLE;

class DataViewPage extends \ADD_Page
{
    function getExpectedPermissions() {
        return array("view");
    }
    function isWebContent() {
        return false;
    }
    function createMenuPath()
    {
        return "api/data-view";
    }
    function createOutput($values = array()) {
        $content = new \ADD_Content_FileView();


        try {
            $runner = new CommandRunner(CLE::sys()->getEnvironment());
            $dvcommand = new \ClinicLE\Manage\Command\DataViewCommand($runner);
            $tempfile = tempnam(null,"cle-dv").".csv";

            $dataType = CLE::sys()->assertParam("t");
            $recordType="encs";
            switch($dataType) {
                case "patient":
                    $recordType = "patients";
                    break;
                case "research":
                    $recordType = "res";
                    break;
            }
            $options = new \ClinicLE\Common\Config\ArrayConfig(array("t"=>$dataType,"r"=>$recordType,
                "f"=>"csv",
                "o"=>$tempfile));

            $dvcommand->runCommand($options);
            $content->setFileName($tempfile);
        }
        catch(\Exception $ex) {
            $content->headers("Status: 400");
        }

        return $content;
    }
}