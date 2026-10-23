<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/03/17
 * Time: 16:24
 */
class ADD_Component_Patient_QuickAdd extends ADD_Component {
    protected $validateMessage = "Sorry cannot create a new patient.";

    function title(&$title) {
        $title = "Quick add new patient";
    }

    function beforeGenerate() {
        $add = ADD::sys();

        $identifier = $add->getParam('identifier');
        $db = ADD::db('patient');

        if(!$identifier) {
            return;
        }
        $pmi = ADD_Util::createPMI();
        $pmiPatient = $pmi->get_patient_by_id($identifier);
        if(!$pmiPatient) {
            throw new \ClinicLE\Patient\Exception\InvalidPatientException("Patient with identifier '$identifier' does not exist");
        }
        $patient = $db->getPatientWithIdentifier($identifier);
        if ($patient) {
            throw new \ClinicLE\Patient\Exception\InvalidPatientException("Patient with identifier '$identifier' already exists");
        }
        $patientData = ADD_PatientImport_Util::mapPmiPatientFields($pmiPatient);
        list($patient,$address,$gp) = $db->createPatientDemographics($patientData);
        $dob = DateTime::createFromFormat("Y-m-d",$patient['date_of_birth']);
        ADD::sys()->setPatientId($patient['id']);
        ADD::web()->addMessage("A new patient record has been created and saved for ".$patient['first_name']." ".$patient['last_name']. "(".$dob->format("d/m/Y").")");
        CLE::web()->afterSubmit($this->_page);
        $add->setState('redirect','/?pid='.$patient['id']);
        return;

    }

    function displayAccess() {
        $page = $this->getPage();
        $cpage = $page->getModule()->getPage("new");

        if (!$cpage->getPermission('create')) {
            throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to create new patients in ADD.");
        }
    }

    function submitAccess($values) {
        $page = $this->getPage();
        $cpage = $page->getModule()->getPage("new");

        if (!$cpage->getPermission('create')) throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to create a patient");
    }

    function afterGenerate() {
        $page = $this->_page;

        $html = $page->html();
        $html->addElement('input',array('name'=>'__patient_id','type'=>'hidden','value'=>''));
        $html->addElement("input",array("type"=>"hidden","value"=>"1","id"=>"ng-reload"));

    }

    function prepareStates() {
        $sys = ADD::sys();
        $sys->setState('new',true);
    }

    function menuPath(&$menuPath) {
        $menuPath="patient/quickadd";
    }
}