<?php

class ADD_Database {
	protected $_options = array();
	protected $_inTrans = false;
	protected static $_conn = null;
	protected static $_dsn = null;
	protected static $_stmtCache = array();
	private static $_dbWrite=null;
	protected static $_dbName = null;
	protected static $_dbHost = null;
	private static $nested=0;
    function getConn() {
           if (!ADD_Database::$_conn) {
               $config = ADD::sys()->getConfig()->assertSectionSettings('Database');
			   ADD_Database::$_dsn = $config['dsn'];
                $dbName = preg_match("/dbname\=(.+)\;{0,1}/",$config['dsn'],$matches);
				ADD_Database::$_dbName = $matches[1];
				$dbHost = preg_match("/host\=(.+)?\;/",$config['dsn'],$matches);
                ADD_Database::$_dbHost = ADD_Util::val(1,$matches,"localhost");
		$options = ADD_Util::val('options',$config);
                if(!$options) $options=array();
                $options['PDO::ATTR_PERSISTENT']=true;
                $conn = new PDO($config['dsn'],ADD_Util::val('user',$config),ADD_Util::val('pass',$config),$options);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                ADD_Database::$_conn = $conn;
            }
            return ADD_Database::$_conn;
        }
	function flushTables() {
		$this->execute("flush tables");
	}
        function getMaxLength($tableName,$columnName) {
            $this->fetchSingleValue("select CHARACTER_MAXIMUM_LENGTH from information_schema.columns where table_schema = ?
                 and table_name = ?
                 and COLUMN_NAME = ?",array(self::$_dbName,$tableName,$columnName)
                    );

            
        }
        
        function getDSN() {
        	return ADD_Database::$_dsn;
		}
		function getDbName() {
			return ADD_Database::$_dbName;
		}
		function getDbHost() {
			return ADD_Database::$_dbHost;
		}
		function makeKeyFromArray($array) {
			return ADD_Util::hash(serialize($array));
		}
	
	function beginTxn() {
		$conn = $this->getConn();
        if ($conn->inTransaction()) {
        	//ADD::log($this)->warn("Already in transaction");
			return $conn;
		}
        $conn -> beginTransaction();
		self::$nested++;
        //if (ADD::sys()->config('log_txn')) ADD::log($this)->info("BEGIN DB transaction (".self::$nested.")");

        return $conn;
    }
    function inTxn() {
        if (!$this->getConn()) return false;
        return $this->getConn()->inTransaction();
    }
    function rollbackTxn() {
    	if (!$this->getConn()->inTransaction()) return;
        $this->getConn() -> rollback();
        //if (ADD::sys()->config('log_txn')) ADD::log($this)->error("ROLLBACK DB transaction");

    }

    function commitTxn() {
    	if (!$this->getConn()->inTransaction()) return;
        $this->getConn() -> commit();
		
        //if (ADD::sys()->config('log_txn')) ADD::log($this)->info("COMMIT DB transaction (".self::$nested.")");
		self::$nested--;
   }
    
    function db($type) {
    	return ADD::sys()->db($type);
	}
	
	function lockTables($names,$type="read") {
            if(!is_array($names)) $names=array($names);
		$tables = array();
		
		foreach($names as $name) {
			$tables[]="$name $type";
			
		}
		$tableList = implode(",",$tables);
		$this->execute("LOCK TABLE $tableList");
	}
	function unlockTables() {
		$this->execute("unlock tables");
	}
	function enableKeys($table) {
		$conn = $this->getConn();
		$stmt = $conn->prepare("alter table $table enable keys");
		$stmt->execute();
	}
	function disableKeys($table) {
		$conn = $this->getConn();
		$stmt = $conn->prepare("alter table $table disable keys");
		$stmt->execute();
	}
	
	function prepare($sql) {
			
		$conn = $this->getConn();
		$stmtHash = ADD_Util::hash($sql);
		
		if (!isset(self::$_stmtCache[$stmtHash])) {
			//CLE::info("caching stmt:$sql = $stmtHash");
			self::$_stmtCache[$stmtHash]=$conn->prepare($sql);
		}
		return self::$_stmtCache[$stmtHash];
	}
	
	function execute($sql,$params=array()) {
		//$log = ADD::log($this);
        //CLE::info("Time before execute:".microtime());
		
		$stmt = $this->prepare($sql);
        if (is_scalar($params)) $params=array($params);

		$response = $stmt->execute($params);
		if (!$response) throw new ADD_DatabaseException("Could not execute SQL statement:".join(";",$stmt->errorInfo()));
        //CLE::info("SQL:".$sql," params:",json_encode($params));
		//CLE::info("Time after execute:".microtime());
		return $stmt;
	}
	function is_assoc($var)
	{
        return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
	}
	function fetchAll($sql,$params=array()) {
		$timeBefore = date("d/m/Y H:i.s");
		
		
		$stmt = $this->execute($sql,$params);
		$fetchRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$timeAfter = date("d/m/Y H:i.s");
		return $fetchRows;
	}
	
	function fetchFirst($sql,$params=array()) {
		$stmt = $this->execute($sql,$params);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row;
	}
	
	function fetchPage($sql,$params,$pg=1,$rows=20) {
		if (strlen($pg)==0) $pg=1;
		if (strlen($rows)==0) $rows=20;
		$startRow = ($pg*$rows)-$rows;
		$limitSql = "LIMIT $startRow,$rows";
		return $this->fetchAll($sql." ".$limitSql,$params);
	}
	
	function fetchSingleValue($sql,$params=array()) {
		$stmt = $this->execute($sql,$params);	
		return $stmt->fetchColumn(0);
	}
	function executeFile($fileName,$params=array()) {
		//ADD::log($this)->debug("execute file:$fileName");
		$conn = $this->getConn();
                $sqlFile = vsprintf(file_get_contents($fileName),$params);
                
               $sqlStatements = explode(";",$sqlFile);
                
		foreach($sqlStatements as $sql) {
                    $sql=trim($sql);
                    if (strlen($sql)==0) continue;
                    //ADD::log()->info("SQL:$sql");
                    $conn->exec($sql);
                }
	}
	function createInsertSql($table,$row) {
		$sql = "insert into $table";
		$fields = array();
		$params = array();
		$pholders = array();
                //$row['id']=null;
		foreach($row as $field => $value) {
			$params[]=$value;
			$pholders[]="?";
			$fields[]=$field;
		}
		$sql.="(".implode(",",$fields).") values (".implode(",",$pholders).")";
		return array($sql,$params);
	}

    function now() {
        return date("Y-m-d H:i:s");
    }

    function updateTimestamps($fieldsToUpdate, &$row) {
    	if(!is_array($fieldsToUpdate)) $fieldsToUpdate = array($fieldsToUpdate);
    	$now = $this->now();
        foreach($fieldsToUpdate as $fup) {
        	$row[$fup]=$now;
		}
	}

	function insert($table,$row, $timestamps=null) {
		if ($timestamps) $this->updateTimestamps($timestamps, $row);
		
		list($sql,$params) = $this->createInsertSql($table,$row);
        
    
		$result = $this->execute($sql,$params);
		$id = $this->getConn()->lastInsertId();
		$row['id']=$id;
		return $row;
	}
	function lastInsertId() {
		return $this->getConn()->lastInsertId();
	}
		
	function update($table,$existing,$replace=null,$timestamps=null) {
		if (!is_array($existing)) {
            $existing=array("id"=>$existing);
        }
		if (!$replace) {
			$replace = $existing;
			unset($replace['id']);
		}
		if ($timestamps) $this->updateTimestamps($timestamps,$replace);
		foreach($replace as $field => $value) {
			$set[]=$field."=?";
			$params[]=$value;
		}
		
		if (!isset($existing['id'])) throw new ADD_DatabaseException("Cannot update because existing '$table' does not have an ID. ".json_encode($existing));
		$params[]=$existing['id'];
                if (!isset($set)) return null;
		$this->execute("update $table set ".implode(",",$set)." where id=?",$params);
		
		foreach($replace as $field => $value) {
			$existing[$field]=$value;
		}
		return $existing;
	}
	
	function deleteByField($table,$field,$value) {
		return $this->execute("delete from $table where $field=?",array($value));
	}
	function selectById($table,$value) {
		return $this->selectFirstByField($table,"id",$value);
	}
	
	function selectAllByField($table,$field, $value) {
		return $this->fetchAll("select * from $table where $field=?",array($value));
	}
	
	function selectFirstByField($table,$field, $value) {
		return $this->fetchFirst("select * from $table where $field=?",array($value));
	}
	
	function matchDataToTable($table,$data) {
		$stmt = $this->execute("SELECT * FROM $table LIMIT 0");
		$columns = array();
		for ($i = 0; $i < $stmt->columnCount(); $i++) {
		    $col = $stmt->getColumnMeta($i);
		    $columns[] = $col['name'];
		}

		$matched = array();
		
		foreach($columns as $column) {
			if (array_key_exists($column,$data)) {
				$matched[$column]=$data[$column];
			}
		}
		
		return $matched;
	}
       
        
        function getVersion() {
            try {
                return $this->fetchSingleValue("select `version` from `version`");
            }
            catch(PDOException $pex) {
                if (preg_match("/Base table or view not found/",$pex->getMessage())) {
                    return "1.0.17";
                }
            }
        }
			
		function __destruct() {
			self::$_stmtCache=null;
		}
		
		  public function makePlaceholders($params) {
        $count = is_array($params) ? count($params) : $params;
        $pholderList = array_fill(0, $count, "?");
        return implode(",", $pholderList);
    }
	function getFromCacheFunction($function, $params) {
        //$log = ADD::log($this);
        $keyParams = $params;
        array_unshift($keyParams,$function);
        array_unshift($keyParams,$this->getDSN());
			
        $key = $this->makeKeyFromArray($keyParams);
        
        if (ADD::sys()->isInCache($key)) {
        	
            return ADD::sys()->getFromCache($key);
        }
        $data = call_user_func_array(array($this, $function), $params);
		
			
        ADD::sys()->putInCache($key, $data);

        return $data;
    }
		  
function doesTableExist($tableName) {
	    $sql = "SELECT COUNT(*)
	    FROM information_schema.tables 
	    WHERE table_schema = ?
	    AND table_name = ?";

	    $count = $this->fetchSingleValue($sql,array($this->getDbName(),$tableName));
	    return $count>0 ? true : false;
	}
}
