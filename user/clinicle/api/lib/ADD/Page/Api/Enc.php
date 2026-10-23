<?php

class ADD_Page_Api_Enc extends ADD_Page_Api {
		
        function apiOutput() {
            $add = ADD::sys();
            
            list($action,$lid,$fid) = $add->getParams(array('action','lid','fid'));
            $id = $add->getParam('id');
            
            $apage = $add -> getPageInModule($lid,$fid);
            
            if (!$apage->getPermission($action)) {
                throw new ADD_ComponentException("Sorry you do not have permission to '$action' ".$apage->getSetting('title')." records");   
            }
             
            $data = array();
            $er = $add->getEncounterManager()->record($apage->getSetting('encounter'),$add->getPatientId());
            
            if ($action !='create') {
                $er->setId($id);
                if (!$er->exists()) {
                    throw new ADD_ComponentException("Encounter record with ID '$id' does not exist");
                }
                $er->retrieve();
            }
            
            if ($action=='view' || $action=='edit') {
                $encData = $er->getEncData();
                $obsData = $er->getObsValues();
                $data = $encData + $obsData;
                $data['__id'] = $id;
            }
            
            if ($action == 'create') {
                $er->setNew(true);
                $add -> setState('new', true);
            }
            
            $data['__action'] = $action;
            
            if ($action == 'delete') {
                $er->delete();
                return array();
            }
            $compCollection = new ADD_ComponentCollection($apage);
            $apage -> initComponents($compCollection);
            $apage -> setupComponents();
            $form = $apage -> getComponent('Form');
            
            foreach ($form->getFields() as $field) {
                if (!$field -> isStored()) continue;
                $field -> populateValue($data, $this);
                $data[$field -> getName()] = $field -> getValue();
            }
            return $data;
        }
        
	
}
