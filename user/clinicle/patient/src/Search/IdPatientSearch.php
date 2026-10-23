<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/05/18
 * Time: 09:24
 */
namespace ClinicLE\Patient\Search;
use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use CLE;

class IdPatientSearch extends AbstractPatientSearch
{

    public function getMissingSearchEntryWarning() {
        return "To search please enter an S number/hospital number/keyword";
    }

    function findPatientsInDatabase($searchParams, $pageNo=1, $rows=20)
    {
        $terms = Util::val("id", $searchParams);
        $output = $this->searchPatients->findPatientsMatchingTerms($terms, $pageNo, $rows);
        return $output;
    }
}