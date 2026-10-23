<?php



class ADD_TooManyUserRecordsException extends Exception {}
class ADD_Database_Record extends ADD_Database {

	function setRecordInUse($id,$recType,$userName,$sessionId){
		$this->execute("update user_record set record_id=?, record_type=? where username=? and session_id=?",array($id,$recType,$userName,$sessionId));
	}
	function isUserLoggedIn($userName, $sessionId) {
		$count = $this->fetchSingleValue("select count(*) from user_record where username=? and session_id=?",array($userName, $sessionId));
		if($count>1) throw new ADD_TooManyUserRecordsException();
		
		return $count == 1 ? true : false;
	}
	function unlockRecordWithId($id) {
		$this->execute("update user_record set record_id=null, record_type=null where id=?",$id);
	}
	function unlockRecordWithSessionId($sessionId) {
		$this->execute("update user_record set record_id=null, record_type=null where session_id=?",$sessionId);
	}
	function hasRecordWithId($id) {
		$count = $this->fetchSingleValue("select count(*) from user_record where id=?",array($id));
		if(count($count)>1) throw new ADD_TooManyUserRecordsException();
		
		return $count==1 ? true : false;
	}
	function isRecordInUse($sessionId, $id, $recType) {
		$count = $this->fetchSingleValue("select count(*) from user_record where record_id=? and record_type=? and session_id!=?",array($id, $recType, $sessionId));
		if(count($count)>1) throw new ADD_TooManyUserRecordsException();
		
		return $count==1 ? true : false;
	}
	
	function whoIsUsingRecord($id,$recType) {
		return $this->fetchFirst("select * from user_record where record_id=? and record_type=?",array($id,$recType));
	}
	function getAllUserRecords() {
		return $this->fetchAll("select * from user_record where logged_out is null or record_id is not null");
	}
	function removeRecordInUse(ADD_EncounterRecord $er) {
		$this->execute("delete from user_record where record_id=? and record_type='encounter'",array($er->getId()));
	}
	function clearRecordInUse(ADD_EncounterRecord $er) {
		$this->execute("update user_record set record_id=null and record_type=null where record_id=? and record_type=?",array($id,$recType));
	}
	function addUserLogin($username,$sessionId) {
		$recs = $this->fetchAll("select * from user_record where session_id=?",$sessionId);
		if(count($recs)>0) return;
		$this->insert("user_record",array("record_id"=>null,"record_type"=>null,"username"=>$username,"logged_in"=>$this->now(),"session_id"=>$sessionId),array("timestamp"));
	}
	function updateUserLogout($username,$sessionId) {
		$this->execute("update user_record set record_id=null,record_type=null,logged_out=? where username=? and session_id=?",array($this->now(),$username,$sessionId));
	}
	function getLoggedInUserRecordsOlderThan($hours) {
		return $this->fetchAll("select * from user_record where (timediff(now(),timestamp)>? and logged_out is null) or (logged_in is not null and logged_out is not null and record_id is not null)",$hours);
	}
}