<?php

use ClinicLE\Common\Config\AbstractConfig;


abstract class ADD_Pathology_DataSource_Warehouse_Base extends ADD_Pathology_DataSource_Base {
	protected static $_conn;
	protected $_downloadFileName;
	protected $_fileSource;
	protected $_patients;
	protected $config;
	protected $locked;
	protected $_singlePatient = false;
    protected $_sinceStatusAchieved = null;
	function __construct(AbstractConfig $config) {
		parent::__construct($config);
		$this->_downloadFileName=$this->_getDownloadFileName();
		
		$config->setSetting("file",$this->_downloadFileName);
		$this->_patients = $this->_config->assertSetting('patients');
		$this->_sinceStatusAchieved = $this->_config->getSetting('date_since');
		$this->config = $config;
        if(count($this->_patients)==1) {
            $this->_singlePatient = true;
        }
		$this->connect();
	}
	function connect() {
		$config = $this->config;
		self::$_conn = new PDO($config->getSetting('dsn'),$config->getSetting('user'),$config->getSetting('pass'));
		self::$_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	function ping() {
		try {
   
			$stmt = self::$_conn->query("select 1");
            $stmt->closeCursor();
		}
		catch(PDOException $ex) {
			ADD::log($this)->info("Ping failed, reconnect to DB");
			$this->connect();
		}
	}
	function query($sql) {
		$this->connect();
		return self::$_conn->query($sql);
	}
	function exec($sql) {
		$this->connect();
		return self::$_conn->exec($sql);
	}
	function prepare($sql) {
		$this->connect();
		return self::$_conn->prepare($sql);
	}
	function wasLocked() {
		return $this->locked;
	}
	function initDataSource() {
		$config = $this->config;
		if($config->getSetting("force_download") || !$this->_doesDownloadFileExist()) {
			$this->setNotAvailable();
			ADD::log($this)->info("Download data to file");
			$this->_downloadDataToFile();
		}
		else {
			ADD::log($this)->info("Download file ".$this->_downloadFileName." already exists");
		}
		$this->_fileSource = new ADD_Pathology_DataSource_File($config);
		$this->setAvailable();
	}
	function isAvailable() {
		$log = ADD::log($this);
        if($this->_singlePatient) {
            $log->info("isAvailable as single patient query is not affected by lock");
            return true;
        }
    $stmt = $this->query("select is_locked from LRBRU.LRBRU_PATHOLOGY_LOCK");
		
		$row = $stmt->fetch();
   $stmt->closeCursor();
		
		if(ADD_Util::val('is_locked',$row)==1) {
			$available = false;
		}
		else {
			$available = true;
		}
   
		return $available;
		
	}

	function setNotAvailable() {
		$log = ADD::log($this);
		if($this->_singlePatient) {
		    $log->info("Ignore setNotAvailable: single patient query is not affected by lock");
		    return;
        }
		$log->info("Set DB as NOT available");
		
		$stmt = $this->query("select is_locked from LRBRU.LRBRU_PATHOLOGY_LOCK");
		$row = $stmt->fetch();
   $stmt->closeCursor();
   
		$server = self::$_conn->quote(ADD::sys()->config("site_host"));
		
		if(!$row) {
			$this->exec("insert into LRBRU.LRBRU_PATHOLOGY_LOCK (is_locked,timestamp,server) values(1,GETDATE(),$server)");

			return;
		}
      
		$this->exec("update LRBRU.LRBRU_PATHOLOGY_LOCK set is_locked=1, timestamp=GETDATE(), server=".$server);

		$this->locked=true;
		$log->info("Lock pathology data view");
	}
   
	function setAvailable() {
		$conn = self::$_conn;
		$log = ADD::log($this);
        if($this->_singlePatient) {
            $log->info("Ignore setAvailable: Single patient query is not affected by lock");
            return;
        }
		$log->info("Set DB as available");
		
		$stmt = $conn->query("select is_locked from LRBRU.LRBRU_PATHOLOGY_LOCK");
		$row = $stmt->fetch();
   $stmt->closeCursor();
		if(!$row) {
			$this->exec("insert into LRBRU.LRBRU_PATHOLOGY_LOCK is_locked=0");
			return;
		}
		$this->exec("update LRBRU.LRBRU_PATHOLOGY_LOCK set is_locked=0");
		$this->locked=false;
		$log->info("Unlock pathology data view");
	}
	
	function _getDownloadFileName() {
		$patients = $this->_config->assertSetting('patients');
		if(count($patients)==0) {
			throw new Exception("No patient ID provided to pathology datasource");
		}
		else {
			ADD::log($this)->info(count($patients)." patient ID(s) provided to pathology datasource");
		}
		$patientDesc = count($patients)>1 ? sha1(serialize($patients)) : $patients[0];
		return $this->_config->getSetting("download_path","/tmp")."/".date("dmY")."-pathology-".$this->_getFileSuffix()."-".$patientDesc.".txt";
	}
	function _getFileSuffix() {}
	function _doesDownloadFileExist() {
		if(file_exists($this->_downloadFileName)) return true;
		return false;
	}
	function getPatients() {
		return $this->_patients;
	}
	
	function _initPatients() {
		$log = ADD::log();
		$log->info("Truncate existing patient list");

		$this->exec("truncate table LRBRU.LRBRU_PATIENTS");
		$patientCount = count($this->_patients);
		$log->info("Adding ".$patientCount." patient identifiers to patient list");
		$sql = "insert into LRBRU.LRBRU_PATIENTS(SYSTEM_NUMBER) VALUES(?)";
		$stmt = $this->prepare($sql);
		
		foreach($this->_patients as $patient) {
			$stmt->execute(array($patient));
		}
	}
		
	function _initQuery() {}
	function _downloadDataToFile() {
		$log = ADD::log();
		$log->info("Downloading data to file ".$this->_downloadFileName);
		if($this->_singlePatient===false) {
            $this->_initPatients();
        }
		$log->info("Initialising retrieval query");
		
		list($stmt,$params) = $this->_initQuery();
		
		$stmt->execute($params);
		$tempFile = fopen($this->_downloadFileName,"w");
		
		$hasAddedHeadings=false;
		$count = 0;
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if(!$hasAddedHeadings) {
				fputcsv($tempFile,array_keys($row),"\t");
				$hasAddedHeadings=true;
			}
			fputcsv($tempFile,array_values($row),"\t");
			$count++;
			if($count%1000==0) {
				$log->info("Added $count lines to download file");
			}
		}
   $stmt->closeCursor();
		fclose($tempFile);
	}
	public function nextTestItem() {
		return $this->_fileSource->nextTestItem();
	}
	function getTestItemTotal() {
		return $this->_fileSource->getTestItemTotal();
	}
}

