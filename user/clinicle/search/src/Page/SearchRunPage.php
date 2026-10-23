<?php

namespace ClinicLE\Search\Page;
use ClinicLE\Search\Output\DefinitionFileOutput;
use ClinicLE\Search\Output\DefinitionOutput;
use ClinicLE\Search\Output\PatientPerRowOutput;
use ClinicLE\Search\Output\StandardPagedOutput;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Util\CommandExecute;
use RandomLib\Factory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\System\Exception\NotifyUserException;
use ClinicLE\System\Service\CoreServices;
use CLE;
use Rhumsaa\Uuid\Uuid;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
class SearchRunPage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $uuid4 =$uuid4 = Uuid::uuid4();
        $uniqueId = $uuid4->toString();

        $searchData = $this->retrieveSearchData($request->query);
        if(!$searchData) {
            return;
        }
        $factory = new DataQueryRunnerFactory(\CLE::svc(SearchServices::GROUP_SET));
        $runner = $factory->createRunner($searchData);
        if(!$runner) {
            throw new \Exception("Runner missing");
        }
        $definitionPath = CLE::sys()->getPath()."/".CLE::sys()->getConfig()->getSection("Search")->get("definition_path");

        $definitionOutput = new DefinitionFileOutput($runner);
        $definitionOutput->setDefinitionPath($definitionPath);
        $definitionOutput->setSearchId($uniqueId);
        $definitionOutput->outputResponse();

        $outputType = $request->query->get("output_type");

        $cmdArgs = array(
            "--complete-file"=>null,
            "--search-id="=>"\"$uniqueId\"",
            "--use-definition"=>null,
            "--page-no="=>$request->query->get("page_no"),
        );
        $fileName = $this->generateFileName();
        switch($outputType) {
            case "export":
                $cmdArgs["--format="]="csv";
                $cmdArgs["--output-filename="]=$fileName;
                break;
            case "email":
                $cmdArgs["--send-email="]=CLE::sys()->getAccessManager()->getUser()->email;
                $cmdArgs["--format="]="csv";
                $cmdArgs["--output-filename="]=$fileName;
                break;
            default:
                $cmdArgs["--format="]="serialized";
                break;
        };

        $command = new CommandExecute("run-search", $cmdArgs);
        $command->setLogConfigFile("log4php/run-search.xml");
        $command->executeInBackground();
        return array(
            "search_id"=>$uniqueId,
            "filename"=>$fileName
        );
    }
    private function generateFileName() {
        $factory = new Factory();
        $generator = $factory->getLowStrengthGenerator();
        $fileName = date("Y-m-d") . "_search_" . $generator->generateString(8, \RandomLib\Generator::CHAR_UPPER_HEX)
            . ".csv";

        return $fileName;
    }
    function retrieveSearchData(ParameterBag $params) {
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

        if(!$ssData->hasSearchData()) {
            return null;
        }

        return $ssData->getSearchData();
    }
    function createMenuPath()
    {
        return "search/run";
    }

}