<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/10/16
 * Time: 17:21
 */

namespace ClinicLE\Manage\Command;


use ClinicLE\Command\AbstractCommand;
use ClinicLE\Common\Config\AbstractConfig;

use ClinicLE\Manage\Design\FormIO;
use ClinicLE\Manage\Design\FormContent;

class FormDesignCommand extends AbstractCommand
{
    function getDescription()
    {
        return "Get form design from database and output FTX file";
    }

    function getOptsList()
    {
        return "f";
    }

    function runCommand(AbstractConfig $options)
    {
        $formPageName = $options->assert("f");
        $moduleName = $options->assert("m");
        $outputFileName = $options->get("o");

        $formIO = new FormIO($moduleName,$formPageName);
        $formContent = new FormContent($formIO);

        $fileContent = $formContent->getFileContent();
        if(!$outputFileName) {
            echo $fileContent;
            return;
        }
        else {
            \CLE::info("Output file to ".$outputFileName);
            file_put_contents($outputFileName,$fileContent);
        }
    }

}