<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Client_Action_EmberPrimaryDiagnosis extends ADD_Client_Action_PrimaryDiagnosis {
    function getNgClick() {
        return \ClinicLE\Client\Util\AngularJS::createCustomCall("emberPrimary",array("row.id","row.primary_diagnosis
		.value"));
    }
}