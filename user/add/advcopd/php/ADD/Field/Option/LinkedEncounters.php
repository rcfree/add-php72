<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LinkedEncounters
 *
 * @author rcf8
 */
class ADD_Field_Option_LinkedEncounters extends ADD_Field_Option_Standard {
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
                    $currStat = ADD_Util::val('current_status',$obs);
                    $item=trim($item);
                    if ($currStat==2) {
                            $item="INACTIVE (".$item.")";
                    }
                    $encOptions[$enc['id']]=$item;
            }
            }
            $this->_optionList=$encOptions;
            $this->_field = $field;
    }
    
    
    function findLabelWithValue($value) {
       $edb = ADD::db('encounter');	
       $encObsNameList = $this->_field->assertSetting('encounter_obs',null);
       $encObsNames = explode(";",$encObsNameList);
            
        $enc = $edb->getEncounter($value);
        $obs = $edb->getObservationsInEncounter($enc,null);
        $item = "";
        
        foreach($encObsNames as $encObsName) {
            $item.=ADD_Util::val($encObsName,$obs,"")." ";
        }
        $currStat = ADD_Util::val('current_status',$obs);
        $item=trim($item);
        if ($currStat==2) {
                $item="INACTIVE (".$item.")";
        }
        return $item;
    } 
}
