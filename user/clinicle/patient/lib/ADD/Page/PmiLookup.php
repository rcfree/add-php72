<?php

class ADD_Page_PmiLookup extends ADD_Page {
	function isWebContent() {
		return false;
	}
    function createMenuPath() {
        return "lookup/pmi";
    }

    function createOutput($values=array()) {
        $add = ADD::sys();
        $pmi = ADD_Util::createPMI(null);
		$content = ADD::web()->newContent('Json');
		
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
        $content->setJsonData($patients);
		return $content;
    }
}
