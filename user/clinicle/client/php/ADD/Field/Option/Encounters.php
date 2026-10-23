<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Field_Option_Encounters extends ADD_Field_Option_Standard {
    function initOptions($field,$page) {
            $dictEnc = $field->assertSetting('dictionary_encounter');
            $edb = ADD::db('encounter');	
            $encs = $edb->getEncountersWithPatientAndType(ADD::sys()->getPatientId(),$dictEnc);
            $encObsList = $field->assertSetting('encounter_obs',null);
            $encObs = explode(";",$encObsList);
            $encOptions = array();
            if ($encs) {
            foreach($encs as $enc) {
                    $obs = $edb->getObservationsInEncounter($enc,null);
                    $item = "";
                    foreach($encObs as $encOb) {
                            $item.=ADD_Util::val($encOb,$obs,"")." ";
                    }
                    
                    $encOptions[$enc['parent_encounter_id']]=$item;
            }
            }
            $this->_optionList=$encOptions;
    }
}
