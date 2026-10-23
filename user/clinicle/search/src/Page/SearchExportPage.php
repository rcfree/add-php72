<?php

namespace ClinicLE\Search\Page;
use ClinicLE\Common\Exception\UserExceptionInterface;
use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Search\Output\SearchExportOutput;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Page\PageResponse\ContentPageResponse;
use ClinicLE\Page\PageResponse\ViewPageResponse;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Security\Access;
use CLE;
use Symfony\Component\HttpFoundation\Response;


/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
class SearchExportPage extends \ADD_Page
{
    function isWebContent()
    {
        return false;
    }

    function createOutput($values = array()) {
        $request = Request::createFromGlobals();
        $runner = $this->createRunner($request->query);
        $output = new SearchExportOutput($runner);
        $searchConfig = CLE::sys()->getConfig()->getSection("Search");
        $output->setDefinitionPath($searchConfig->getSetting("definition_path"));
        $output->setExportPath($searchConfig->getSetting("export_path"));
        $response = $output->outputResponse();
        return $response;
    }

    function createRunner(ParameterBag $params) {
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

        if(!$ssData->hasSearchData()) {
            return array();
        }

        $searchData = $ssData->getSearchData();

        $groupSet = \CLE::svc(SearchServices::GROUP_SET);

        $factory = new DataQueryRunnerFactory($groupSet);
        return $factory->createRunner($searchData);
    }

    function createMenuPath() {
        return "search/export";
    }
    function afterSubmit(\ADD_Page $submitPage) {

    }

    function processError(UserExceptionInterface $ex) {
        CLE::web()->addError($ex);
        return new ViewPageResponse(CLE::web()->getThemeView("error"),array("error"=>$ex));
    }
    function createAccess()
    {
        return new Access();
    }
    function createTitle() {
        return "Export run";
    }
    function finaliseOutput($output) {
        if(is_a($output,Response::class)) {
            return $output;
        }
        return parent::finaliseOutput($output);
    }
}