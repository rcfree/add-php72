<?php



class ADD_Database_User extends ADD_Database {
	static $_conn;
	static protected $_dbHost;
	static protected $_dbName;
	static protected $_dsn;
	
	function getConn() {
       	if (ADD_Database_User::$_conn) {
       		return ADD_Database_User::$_conn;
	   	}
		$add = ADD::sys();
	   	$configPath = $add->getPath()."/config/".$add->getProfile();
       	$ciPath = $configPath."/ci/database.php";
		global $db;
		require_once($ciPath);
		$dbDefault = $db['default'];
		
		$dbName = $dbDefault['database'];
		self::$_dbName = $dbName;
			
		$dbHost = ADD_Util::val('hostname',$dbDefault);
        self::$_dbHost = $dbHost;
		
		$dsn = "mysql:";
		$dsn.="host=".$dbDefault['hostname'].";";
		$dsn.="dbname=".$dbName;
		self::$_dsn = $dsn;
		
        $conn = new PDO($dsn,$dbDefault['username'],$dbDefault['password']);
		self::$_conn = $conn;
		return $conn;
    }
	
	function deleteSessionsWithSessionId($sessionId) {
		$userData = '%"add_sessionid";s:23:"$sessionId"%';
		$this->execute("delete from sessions where user_data like ?",$userData);
	}
}