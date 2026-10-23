<?php

class ADD_Component_PmiLookup extends ADD_Component {
    function contentType(&$contentType) {
	$contentType="Json";
    }
	
    function menuPath(&$menuPath) {
        $menuPath="lookup/pmi";
    }

    function output() {
        $add = ADD::sys();
        $page = $this->_page;
        $web = ADD::web();
        $pmi = ADD_Util::createPMI(null);
		
        $id = $add->getParam('id');
        $patients = array();
        try {
            $records = $pmi->get_patient_records($add->getParams());

            foreach($records as $record) {
                $patients[]=$record;
            }
            ADD::db('audit')->auditSearch($add->getParams());
        }
        catch(Exception $ex) {
            $patients['error']=$ex->getMessage();
        }
        $content = $web->getContent();
        $content->setJsonData($patients);
    }
}
