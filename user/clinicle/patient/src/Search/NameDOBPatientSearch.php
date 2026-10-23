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

class NameDOBPatientSearch extends AbstractPatientSearch
{

    public function getMissingSearchEntryWarning() {
        return "To search patients in the hospital system as well as ADD please enter a first name, last name and date of birth";
    }

    function findPatientsInDatabase($searchParams, $pageNo=1, $rows=20)
    {
        list($fname,$lname,$dob) = Util::vals(array("fname","lname","dob"),$searchParams);
        if(Util::isEmpty($fname) && Util::isEmpty($lname) && Util::isEmpty($dob)) {
            throw new \InvalidArgumentException("Please enter one or more search criteria");
        }
        $params = array(
            "first_name"=>$fname,
            "last_name"=>$lname,
            "dob"=>$dob
        );

        $output = $this->searchPatients->findPatientsMatchingNameAndDob($params, $pageNo, $rows);
        return $output;
    }
}