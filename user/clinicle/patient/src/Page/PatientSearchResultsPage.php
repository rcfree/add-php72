<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/05/18
 * Time: 08:48
 */
namespace ClinicLE\Patient\Page;

use ClinicLE\Common\Util;
use ClinicLE\Patient\Search\IdPatientSearch;
use ClinicLE\Patient\Search\NameDOBPatientSearch;
use ClinicLE\Patient\Search\PatientSearchTypes;
use ClinicLE\Search\Page\AbstractSearchPage;
use ClinicLE\Security\Exception\AccessFailedException;
use ClinicLE\System\Exception\NotifyUserException;
use Symfony\Component\HttpFoundation\Request;
use CLE;
class PatientSearchResultsPage extends AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $params = $request->query;
        $searchType = $params->get("search_sel");
        $sys = CLE::sys();
        if (!$sys->getPageFromPim("patient:search")) {
            throw new AccessFailedException("Sorry you do not have permission to search patients.");
        }
        try {
            $pmi = \ADD_Util::createPMI($this);

        }
        catch(\Exception $exception) {
            CLE::web()->addWarning("The UHL patient lookup service is not working. Please add patients manually. They will be updated automatically in the future.");
        }

        $permissions = array(
            "create"=> $sys->getPageFromPim('patient:new')->getPermission('create'),
            "delete"=> $sys->getPageFromPim('patient:delete')->getPermission('delete'),
            "purge"=>$sys->getPageFromPim('patient:purge')->getPermission('purge')
        );

        if ($searchType == PatientSearchTypes::NAME_DOB) {
            $patientSearch = new NameDOBPatientSearch($pmi,CLE::db('search_patient'), $permissions);
        }
        else {
            $patientSearch = new IdPatientSearch($pmi, CLE::db('search_patient'), $permissions);
        }
        $pageNo = $request->get("page_no");
        $rows = $request->get("rows");
        if(!$rows) {
            $rows = 20;
        }
        return $patientSearch->getSearchResults($params->all(), $pageNo, $rows);
    }

    function createMenuPath()
    {
        return "search/patients";
    }
}