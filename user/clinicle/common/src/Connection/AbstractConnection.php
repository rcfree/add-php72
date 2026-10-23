<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Common\Connection;
use ClinicLE\Common\Util;
use ClinicLE\Data\Util\DatabaseUtil;
use PDO;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\Common\Log;
abstract class AbstractConnection {
	private $stmtCache = array();
	protected $dsn;
	private $dbName;
	private $dbHost;
	protected $options = array();
	private $pdo;
	protected $config;
	protected $user;
	protected $pass;

	function getPDO() {
		if($this->pdo) {
			try {
				$this->pdo->query("do 1;");
			}
			catch(\PDOException $ex) {
				Log::info("Reconnect to database");
				if(preg_match("/gone away/",$ex->getMessage())) {
					$this->pdo = null;
                    $this->stmtCache = array();
				}
				else {
					throw $ex;
				}
			}
		} 
	    if(!$this->pdo) {
			$this->populateFromDSN();
            $pdo = null;
            try {
                $pdo = new PDO($this->dsn, $this->user, $this->pass, $this->options);
            }
            catch(\Exception $ex) {
                if (preg_match("/Connection refused/", $ex->getMessage())) {
                    throw new \Exception("Unable to connect to database server");
                }
                throw $ex;
            }
			$pdo->setAttribute(PDO::ATTR_PERSISTENT,true);
			$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			$this->pdo = $pdo;
	    }
		
	    return $this->pdo;
    }
	
	function populateFromDSN() {
            if($this->dbName) {
                return;
            }
	    preg_match("/dbname\=(.+)\;{0,1}/",$this->dsn,$matches);
	    if(!isset($matches[1])) throw new NullValueException("Database name is not defined in the DSN");
	    $this->dbName = $matches[1];
	    preg_match("/host\=(.+)?\;/",$this->dsn,$matches);
	    $this->dbHost = Util::val(1,$matches,"localhost");
	}
	
        function getDSN() {
	    return $this->dsn;
	}
	function getDbName() {
	    return $this->dbName;
	}
	function getDbHost() {
	    return $this->dbHost;
	}
	function getOptions() {
	    return $this->options;
	}
	function setOptions($options) {
	    $this->options = $options;
	}
	function beginTxn() {
		$conn = $this->getPDO();
        if ($conn->inTransaction()) {
        	return $conn;
		}
        $conn -> beginTransaction();

        return $conn;
    }
    function inTxn() {
        if (!$this->getPDO()) return false;
        return $this->getPDO()->inTransaction();
    }
    function rollbackTxn() {
    	if (!$this->getPDO()->inTransaction()) return;
        $this->getPDO() -> rollback();
    }

    function commitTxn() {
    	if (!$this->getPDO()->inTransaction()) return;
        $this->getPDO() -> commit();
   }
	/**
	 * 
	 * @param type $sql
	 * @return type
	 */
	function prepare($sql) {
			
		$conn = $this->getPDO();
		$stmtHash = Util::hash($sql);
		if (!isset($this->stmtCache[$stmtHash])) {
			$this->stmtCache[$stmtHash]=$conn->prepare($sql);
		}
		return $this->stmtCache[$stmtHash];
	}
	
	
	/**
	 * 
	 * @param type $sql
	 * @param array $params
	 * @return PDOStatement
	 * @throws ADD_DatabaseException
	 */
	function execute($sql,$params=array()) {
		
		$stmt = $this->prepare($sql);
        if (is_scalar($params)) $params=array($params);
        #\CLE::info("execute:$sql\nparams:",$params);
		$stmt->execute($params);
		return $stmt;
	}
	
	/**
	 * 
	 * @param type $sql
	 * @param array $params
	 * @return array
	 */
	function fetchAll($sql,$params=array(), $fetchType = PDO::FETCH_ASSOC) {
		$stmt = $this->execute($sql,$params);
		$fetchRows = $stmt->fetchAll($fetchType);
		return $fetchRows;
	}
	
	/**
	 * 
	 * @param type $sql
	 * @param array $params
	 * @return array
	 */
	function fetchFirst($sql,$params=array(), $fetchType = PDO::FETCH_ASSOC) {
		$stmt = $this->execute($sql,$params);
		$row = $stmt->fetch($fetchType);
                return $row;
	}
	
	function fetchPage($sql,$params,$pg=1,$rows=20) {
		if (strlen($pg)==0) $pg=1;
		if (strlen($rows)==0) $rows=20;
		$startRow = ($pg*$rows)-$rows;
		$limitSql = "LIMIT $startRow,$rows";
		return $this->fetchAll($sql." ".$limitSql,$params);
	}
	
	/**
	 * 
	 * @param type $sql
	 * @param array $params
	 * @return string
	 */
	function fetchSingleValue($sql,$params=array()) {
		$stmt = $this->execute($sql,$params);	
		return $stmt->fetchColumn(0);
	}
	
	private function createInsertSql($table,$row) {
		$sql = "insert into `$table`";
		$fields = array();
		$params = array();
		$pholders = array();
                //$row['id']=null;
		foreach($row as $field => $value) {
			$params[]=$value;
			$pholders[]="?";
			$fields[]=$field;
		}
		$sql.="(`".implode("`,`",$fields)."`) values (".implode(",",$pholders).")";
		return array($sql,$params);
	}
	private function updateTimestamps($fieldsToUpdate, &$row) {
    	if(!is_array($fieldsToUpdate)) $fieldsToUpdate = array($fieldsToUpdate);
    	$now = $this->timestamp();
        foreach($fieldsToUpdate as $fup) {
        	$row[$fup]=$now;
		}
	}

	/**
	 * 
	 * @param string $table
	 * @param array $row
	 * @param array|null $timestamps
	 * @return type
	 */
	function insert($table,$row, $timestamps=null) {
		$pdo = $this->getPDO();
		if ($timestamps) $this->updateTimestamps($timestamps, $row);
		
		list($sql,$params) = $this->createInsertSql($table,$row);
        
		$result = $this->execute($sql,$params);
		$id = $pdo->lastInsertId();
		$row['id']=$id;
		
		return $row;
	}
	/**
	 * 
	 * @return integer
	 */
	function getLastInsertId() {
		return $this->getPDO()->lastInsertId();
	}

	/**
	 * 
	 * @param string $table
	 * @param array|string $existing
	 * @param array $replace
	 * @param array|null $timestamps
	 * @return null
	 * @throws ADD_DatabaseException
	 */
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
		
		if (!isset($existing['id'])) throw new \InvalidArgumentException("Cannot update because existing '$table' does not have an ID. ".json_encode($existing));
		$params[]=$existing['id'];
                if (!isset($set)) return null;
		$this->execute("update `$table` set ".implode(",",$set)." where id=?",$params);
		
		foreach($replace as $field => $value) {
			$existing[$field]=$value;
		}
		return $existing;
	}
	
	/**
	 * 
	 * @param type $table
	 * @param type $field
	 * @param type $value
	 * @return type
	 */
	function deleteByField($table,$field,$value) {
		return $this->execute("delete from $table where $field=?",array($value));
	}
	/**
	 * 
	 * @param type $table
	 * @param type $value
	 * @return type
	 */
	function selectById($table,$value) {
		return $this->selectFirstByField($table,"id",$value);
	}
	
	/**
	 * 
	 * @param type $table
	 * @param type $field
	 * @param type $value
	 * @return type
	 */
	function selectAllByField($table,$field, $value) {
		return $this->fetchAll("select * from `$table` where $field=?",array($value));
	}
	
	/**
	 * 
	 * @param type $table
	 * @param type $field
	 * @param type $value
	 * @return type
	 */
	function selectFirstByField($table,$field, $value) {
		return $this->fetchFirst("select * from `$table` where $field=?",array($value));
	}
	
	function __destruct() {
		$this->stmtCache=null;
	}
	
	function getFromStmtCache() {
	    
	}
	
	function putInStmtCache() {
	    
	}
	
	static function hashQuery($sql) {
	
	}
	/**
	 * 
	 * @param type $table
	 * @return bool
	 */
	function doesTableExist($tableName) {
        if(isset($this->_tableExists[$tableName])) {
			return $this->_tableExists[$tableName];
		}
	    $sql = "SELECT COUNT(*)
	    FROM information_schema.tables 
	    WHERE table_schema = ?
	    AND table_name = ?";
	    
	    $count = $this->fetchSingleValue($sql,array($this->getDbName(),$tableName));
	    $this->_tableExists[$tableName] = $count>0;
		return $this->_tableExists[$tableName];
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
	
	function timestamp() {
        return date("Y-m-d H:i:s");
    }

    function enableKeys($table) {
        $this->execute("alter table $table enable keys");
    }
    function disableKeys($table) {
        $this->execute("alter table $table disable keys");
    }
  function store($tableName, $rowData, $matchField="id") {
    if(!isset($rowData[$matchField])) {
      throw new \InvalidArgumentException("Cannot store row data as it does not contain a '$matchField' field");
    }
    $existing = $this->selectAllByField($tableName,$matchField,$rowData[$matchField]);
    if(count($existing)==0) {
      return $this->insert($tableName,$rowData);
    }
    return $this->update($tableName, $rowData[$matchField], $rowData);
  }
    function getMaxLength($tableName,$columnName) {
        $this->fetchSingleValue("select CHARACTER_MAXIMUM_LENGTH from information_schema.columns where table_schema = ?
                 and table_name = ?
                 and COLUMN_NAME = ?",array($this->getDbName(),$tableName,$columnName)
        );


    }
    	
}
