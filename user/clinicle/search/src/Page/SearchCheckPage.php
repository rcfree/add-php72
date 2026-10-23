<?php

namespace ClinicLE\Search\Page;
use ClinicLE\Search\Output\OutputFiles;
use ClinicLE\Search\Output\PatientPerRowOutput;
use ClinicLE\Search\Output\StandardPagedOutput;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Util\CommandExecute;
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
class SearchCheckPage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $searchId = $request->query->get("search_id");
        $resultsPath = CLE::sys()->getPath()."/".CLE::sys()->getConfig()->getSection("Search")->get("definition_path")
            ."/".$searchId;
        $checkFile = $resultsPath."/".OutputFiles::COMPLETE_FILE;
        if(file_exists($checkFile)) {
            return array("complete"=>true);
        }
        return array("running"=>true);
    }

    function createMenuPath()
    {
        return "search/check";
    }

}