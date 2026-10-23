<?php

class ADD_Component_CheckUnique extends ADD_Component implements ADD_Component_AjaxInterface {
    function ajaxFunction($values,ADD_Page $callPage) {
        $val = $values['ilh_identifier'];
        if(ADD_Util::isEmpty($val)) return;
        $edb = ADD::db('encounter');
        $er = $callPage->getEncounterRecord();
        $er->retrieve();
        $ecount = $edb->countObservationsWithValue("ilh_identifier",$val,"res-contact-details",$er->getId());

        if ($ecount>=1) throw new ADD_AjaxFieldException("<i>$val</i> has already been used <b>$ecount</b> ".ADD_Util::pluralize($ecount,"time"),"ILH identifier","ilh_identifier");

        $bcount = $edb->fetchSingleValue("SELECT COUNT(*) FROM ilh_existing_id WHERE id = ?",$val);
        if($bcount==0) ADD::web()->addWarning("The ILH identifier <i>$val</i> is not in the list of pre-printed barcodes");
    }

    function ajaxInputs(ADD_Page $callPage) {
        return array('ilh_identifier');
    }
} 