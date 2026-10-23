<?php
class ADD_UserLogin {
	private $_sessionId = null;
    private $_user = null;
	
	function __construct($user,$sessionId) {
		$this->_user = $user;
		$this->_sessionId = $sessionId;
	}
	function login() {
		ADD::db('audit')->auditUser("login", $this->_user);
		
		$this->_lockTable();
		ADD::db('record')->addUserLogin($this->getUserName(),$this->_sessionId);
		$this->_unlockTable();			
	}
	function logout() {
		ADD::debug('logout');
		ADD::db('audit')->auditUser("logout", $this->_user);
		$this->_lockTable();
		ADD::db('record')->updateUserLogout($this->getUserName(),$this->_sessionId);
		$this->_unlockTable();
	}
	function _determineRecordFromPage($page) {
		if($page->getModule()->getName()=="patient") {
			$id = ADD::sys()->getPatientId();
			$recType = "patient";
		}
		else {
			$parent = $page->getParent();
			if(!$parent) return;
			if(!method_exists($parent,"getEncounterRecord")) return;
			$er = $page->getParent()->getEncounterRecord(ADD::sys()->getParam('eid'));
			if($er) {
				$id = $er->getId();
			}
			else {
				$id = ADD::sys()->getPatientId();
			}
			$recType = $page->getModule()->getName().".".$page->getName();
		}
		return array($id,$recType);
	}
	function unlockRecordWithId($id) {
		
		$udb = ADD::db('record');
		$this->_lockTable();
		if(!$udb->hasRecordWithId($id)) {
			$this->_unlockTable();	
			throw new Exception("Record with ID '$id' does not exist");
		}
		$udb->unlockRecordWithId($id);
		$this->_unlockTable();
		
		return true;
	}
	
	function unlock() {
		$udb = ADD::db('record');
		$this->_lockTable();
		$udb->unlockRecordWithSessionId($this->_sessionId);
		$this->_unlockTable();
		return true;
	}
	
	function getUserName() {
		return $this->_user->username;
	}
	function whoIsUsingRecordPage($page) {
		$udb = ADD::db('record');
		
		if(!$udb->isUserLoggedIn($this->getUserName(),$this->_sessionId)) {
			$udb->addUserLogin($this->getUserName(),$this->_sessionId);
			return;
		}
		
		list($id,$recType) = $this->_determineRecordFromPage($page);
		
		//if record is not in use by someone else set this user as current user
		if($udb->isRecordInUse($this->_sessionId,$id,$recType)===false) {
			$this->_unlockTable();
			return null;
		}
		
		//if so then set state as view only
		ADD::sys()->setState(ADD_System::READONLY,true);
		ADD::sys()->setState(ADD_System::AUTOREFRESH,ADD::sys()->config('in_use_refresh_time'));
		ADD::sys()->setState(ADD_System::INUSE,true);
		
		//who is editing the record?
		$currentUser = $udb->whoIsUsingRecord($id,$recType);
		$this->_unlockTable();
		return $currentUser;
	}
	
	function lockRecordPage($page) {
		list($id,$recType) = $this->_determineRecordFromPage($page);
		ADD::db('record')->setRecordInUse($id,$recType,$this->getUserName(),$this->_sessionId);
		$this->_unlockTable();
	}
	function hasSessionId() {
		return $this->_sessionId ? true : false;
	}
	static function instanceFromAccessManager(ADD_AccessManager $accessManager) {
		$user = $accessManager->getUser();
		$sessionData = $accessManager->getSessionData();
		$sessionId = ADD_Util::val("add_sessionid",$sessionData);
		return new ADD_UserLogin($user,$sessionId);
	} 
	function _lockTable() {
		ADD::db('record')->lockTables("user_record","write");
	}
	function _unlockTable() {
		ADD::db('record')->unlockTables();
	}
}