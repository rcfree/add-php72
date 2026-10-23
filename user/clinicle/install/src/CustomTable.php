<?php
namespace ClinicLE\Install;
use ADD_Page;
use ADD;
use ADD_Util;

class CustomTable {
	private $_formSync;
	private $_page;
	function __construct(FormSync $formSync, ADD_Page $page) {
		$this->_formSync = $formSync;
		$this->_page = $page;
	}
	function createOrUpdate() {
            	$tableName = $this->createTableName();
		if(!$this->doesTableExist() && !$this->_formSync->isNew()) {
			$this->createDataTable(true);
			return;
		}
		if($this->_formSync->isNew()) {
            $this->createDataTable(false);
        }
        else {
            $this->updateDataTable();
		}
	}
	function createTableName() {
		$encForm = $this->_formSync->getEncForm();
		$modName = $this->_page->getModule()->getSuperModule()->getName();
                if(ADD_Util::isEmpty($encForm['type'])) {
                    throw new \InvalidArgumentException("Encounter name is empty in enc form");
                }
        return "e_".$encForm['type'];
	}
	function doesTableExist() {
		$tableName = $this->createTableName();
		ADD::log($this)->info("tableName:".$tableName);
		$tableRows = ADD::db()->fetchAll("show tables like ?",array($tableName));
		return count($tableRows)>0 ? true : false;
	}
    function createDataTable($includeAllFields=false) {
		$formSync = $this->_formSync;
		$page = $this->_page;
        $modName = $page->getModule()->getSuperModule()->getName();
        
		$sql = "create table `".$this->createTableName()."`";

        $sqlFields = array(
            "`id` int(11) NOT NULL UNSIGNED AUTO_INCREMENT",
            "`patient_id` int(11) UNSIGNED NOT NULL",
            "`section_id` int(11) UNSIGNED NOT NULL",
	   );
		
	    $form = $this->_formSync->getFormFile();

	    $this->appendSqlFields("",$form->getFields(),$sqlFields);
       
	$sqlFields = array_merge($sqlFields,array("`badge` varchar(20) DEFAULT NULL",
            "`is_complete` tinyint(4) DEFAULT '0'",
            "`is_locked` tinyint(4) DEFAULT '0'",
            "`is_deleted` tinyint(4) DEFAULT '0'",
            "`is_invalid` tinyint(4) DEFAULT '0'",
            "`is_autosave` tinyint(4) DEFAULT '0'",
            "`is_verified` tinyint(4) DEFAULT '0'",
            "`revision_no` int(11) NOT NULL",
            "`date_created` datetime DEFAULT NULL",
            "`date_updated` datetime DEFAULT NULL",
			"PRIMARY KEY (`id`)")
		);
		
        $sql.="(".implode(",\n",$sqlFields).") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		//ADD::log()->info("createSql:".$sql);
        ADD::db()->execute($sql);
    }
	function appendSqlFields($prefix, $fields,&$sqlFields,$recordNotExisting=false,$ignoreExistingField=false) {
	    $notExistingFields = array();
	    $log = ADD::log($this);
	    $tableToCheck = $this->createTableName();
	    foreach($fields as $field) {
		    if(is_a($field,"ADD_Field_Container") && !$field->isStored()) continue;
		    if($recordNotExisting && !$this->doesFieldExistInTable($field->getName())) {
			$log->info("Field '".$field->getName()."' does not currently exist in table '".$tableToCheck."'");
			$notExistingFields[]=$field;
			continue;
		    }
		    if($ignoreExistingField && $this->doesFieldExistInTable($field->getName())) {
			$log->info("Field '".$field->getName()."' already exists in table '".$tableToCheck."'");
			continue;
		    }
		    if(!$field->isStored()) continue;
		    try {
			$dataType = $field->getDataType();
			$dataLength = $field->getDataLength();
		    }
		    catch(Exception $ex) {
			$dataType="text";
			$dataLength=null;
		    }

		    if(!$dataType && !$dataLength) continue;
		    $sqlFields[] =  $prefix."`".$field->getName()."` ".$dataType.($dataLength ? "(".$dataLength.")" : "");
	    }
	    return $notExistingFields;
	}
	
	function doesFieldExistInTable($fieldName) {
	    $tableName = $this->createTableName();
	    $columns = ADD::db()->fetchAll("SHOW COLUMNS FROM `$tableName` LIKE '$fieldName';");
	    return count($columns)>0 ? true : false;
	}
	function updateDataTable() {
		$formSync = $this->_formSync;
		
        $encForm = $formSync->getEncForm();
        $sql = "alter table `e_".$encForm['type']."`";

        $sqlFields = array();
	
	$notPresentFields = $this->appendSqlFields("modify column ",$formSync->getUpdated(),$sqlFields,true);
        $createdFields = array_merge($notPresentFields,$formSync->getCreated());
	$this->appendSqlFields("add column ",$createdFields,$sqlFields,false,true);
	$notPresentFields = $this->appendSqlFields("delete column ",$formSync->getDeleted(),$sqlFields,true);
	if(count($sqlFields)==0) {
	    ADD::log($this)->info("No changes to table ".$this->createTableName());
	    return;
	}
	$sql.=" ".implode(",\n",$sqlFields);
	ADD::log($this)->info("update sql:$sql");
	ADD::db()->execute($sql);
    }
}