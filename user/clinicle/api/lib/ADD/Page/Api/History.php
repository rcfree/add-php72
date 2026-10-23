<?php

class ADD_Page_Api_History extends ADD_Page_Api {
		
        function apiOutput() {
            $add = ADD::sys();
            
            list($fieldName,$encId) = $add->getParams(array('name','id'));
			
            $db = ADD::db('history');
            
            $audits = $db->getHistoryForFieldInEncounter($fieldName,$encId);
            return $audits;
        }
        
	
}
