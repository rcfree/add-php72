<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 01/11/17
 * Time: 21:07
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Search\Query\QueryRunner;

class DefinitionFileOutput extends AbstractFileOutput
{
    function __construct(QueryRunner $runner) {
        parent::__construct($runner);
        $this->definitionOutput = new DefinitionOutput($runner);
    }
    function outputResponse()
    {
        $definitionData = $this->definitionOutput->outputResponse();
        $definitionPath = $this->createDefinitionPath();
        file_put_contents($definitionPath."/".OutputFiles::DEFINITION_FILE,json_encode($definitionData));
    }
}