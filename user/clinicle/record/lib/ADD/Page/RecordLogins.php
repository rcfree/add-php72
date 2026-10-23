<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Page_RecordLogins extends ADD_Page_Api {
	function createMenuPath() {
		return "record/logins";
	}
	function apiOutput() {
		$userRecords = ADD::db('record')->getAllUserRecords();
		
		foreach($userRecords as &$userRecord) {
			$recordId = $userRecord['record_id'];

			$userRecord['unlockable']=$recordId ? true : false;
		}
		
		return $userRecords;
	}
}