<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Client_Action_AcutePrimaryDiagnosis extends ADD_Client_Action_PrimaryDiagnosis {
	function getNgClick() {
		return "acutePrimary(row.id,row.primary_diagnosis.value)";
	}
}