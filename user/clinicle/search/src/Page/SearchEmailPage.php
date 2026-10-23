<?php

namespace ClinicLE\Search\Page;
use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Search\Output\EmailSearchExportOutput;
use ClinicLE\System\Util\CommandExecute;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\Util\DelimitedDataWriter;
use ClinicLE\Security\AccessManager;
use ClinicLE\Service\Email\Email;
use ClinicLE\System\Service\CoreServices;
use CLE;
use ClinicLE\Search\Query\QueryExport;
use RandomLib\Factory;


/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
class SearchEmailPage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $uuid4 =$uuid4 = Uuid::uuid4();
        $uniqueId = $uuid4->toString();
        $templatePath = CLE::sys()->getPath()."/".CLE::sys()->getConfig()->getSection("Search")->get("definition_path");
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

        if(!$ssData->hasSearchData()) {
            return;
        }

        $searchData = $ssData->getSearchData();

        file_put_contents($templatePath."/$uniqueId",json_encode($searchData));

        $command = new CommandExecute("run-search",
            array(
                "--search-id="=>"\"$uniqueId\"",
                "--use-definition"=>null,
                "--format="=>"csv",
                "--send-email="=>CLE::sys()->getAccessManager()->getUser()->email
            )
        );
        $command->setLogConfigFile("log4php/email-search.xml");
        $command->executeInBackground();
        return array(
            "success"=>true
        );
    }

    function createMenuPath()
    {
        return "search/email";
    }

}