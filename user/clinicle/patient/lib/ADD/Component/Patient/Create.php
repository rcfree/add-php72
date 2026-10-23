<?php

class ADD_Component_Patient_Create extends ADD_Component {
    protected $_componentList = array("RequiredFields_Validate",'Form_File','OptionContainer','SaveButton');

    protected $validateMessage = "Sorry cannot create a new patient.";

    function title(&$title) {
        $title = "Create new patient";
    }
    function breadcrumb(&$breadcrumb) {
        $breadcrumb = array("Patient","Create new patient");
    }
    function menu($options) {
        if (ADD::sys()->getAccessManager()->getCurrentPage()=="patient/new") $options->addTextElement("p","Options will be shown after you save the patient (click 'Save Data').",array("class"=>"text-info"));
    }

    function beforeGenerate() {
        $add = ADD::sys();

        $save = $add->getParam('save');
        $identifier = $add->getParam('identifier');
        $db = ADD::db('patient');

        if($save && $identifier) {
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
            CLE::sys()->getContext()->afterSubmit($this->_page);
            $add->setState('redirect','/?pid='.$patient['id']);
            return;
        }
        parent::beforeGenerate();
        $page = $this->_page;

        $add->setState('noSidebar',true);
        if (!$identifier) {
            $page->addJavascript("jQuery('#tabs-tabs a[href=\"#search\"]').tab('show');");
            return;
        }
        $patient = $db->getPatientWithIdentifier($identifier);
        if ($patient) {

            $patientMessage = $this->beforeSubmit($patient,$page);
            if (count($patientMessage)>0) throw new \ClinicLE\Patient\Exception\InvalidPatientException(join("<br/>",$patientMessage));
        }
        $html = $page->html();
        $html->startElement("div",array("style"=>"display:none"));

        $pmi = ADD_Util::createPMI($this);
        $record = $pmi->get_patient_by_id($identifier);
        foreach($record as $name => $value) {
            $html->addElement("input",array("type"=>"text","value"=>$value,'id'=>"{$name}.{$identifier}",'name'=>"patient{$identifier}"));
        }
        $html->endElement("div");

        $page->addJavascript("populatePatient('{$identifier}');");
        $page->addMessage("The patient demographics have been populated from the hospital patient system.<br/>Please click the 'Save record' button to save them in ADD.");
    }

    function displayAccess() {
        $page = $this->_page;

        if (!$page->getPermission('create')) {
            throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to create new patients in ADD.");
        }
    }

    function submitAccess($values) {
        $page = $this->_page;

        if (!$page->getPermission('create')) throw new \ClinicLE\Security\Exception\AccessFailedException("Sorry you do not have permission to create a patient");
    }

    function afterGenerate() {
        $page = $this->_page;

        $html = $page->html();
        $html->addElement('input',array('name'=>'__patient_id','type'=>'hidden','value'=>''));
        $html->addElement("input",array("type"=>"hidden","value"=>"1","id"=>"ng-reload"));

        parent::afterGenerate();
    }

    function prepareStates() {
        $sys = ADD::sys();
        $sys->setState('new',true);
    }

    public function submit($values) {
        $add = ADD::sys();
        $page = $this->_page;

        $pdb = ADD::db('patient');
        $adb = ADD::db('audit');
        $pat=null;
        try {
            list($pat,$addr,$gp) = $pdb->createPatientDemographics($values);
        }
        catch(ADD_DataTruncatedException $ex) {
            $page->addWarning($ex->getMessage());
            $pat = $ex->getPatient();
        }
        $adb->auditPatient("create",$pat,$addr,$gp);
        $page->addMessage("A new patient was created successfully");
        $add->setPatientId($pat['id']);
        $query=array("pid"=>$pat['id']);
        ADD::db('audit')->insert("recent_patient",array("patient_id"=>$pat['id'],"username"=>$add->getAccessManager()->getUser()->username),array("timestamp"));

        $redirect = ADD_Util::uri("patient/demographics",$query,true);
        return array('redirect'=>$redirect);
    }

    function menuPath(&$menuPath) {
        $menuPath="patient/new";
    }

    function expectedPermissions(&$expPerms) {
        $expPerms = $expPerms + array('create');
    }
}
?>
