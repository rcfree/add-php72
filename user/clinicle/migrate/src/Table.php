<?php

namespace ClinicLE\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\Common\Util;

class Table {
	private $fields=array();
	private $keys=array();
	private $primaryKey;
	private $tableName;
	private $createStatement;

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

	function __construct($createStatement=null) {
		$this->createStatement = $createStatement;
	}
	
	/**
	 * 
	 * @param type $tableName
	 */
	function setTableName($tableName) {
		$this->tableName = $tableName;
	}
	
	/**
	 * 
	 * @return type
	 */
	function getTableName() {
		return $this->tableName;
	}
	
	/**
	 * 
	 * @return type
	 */
	function getFields() {
		return $this->fields;
	}
	/**
	 * 
	 * @param type $fieldName
	 * @return type
	 */
	function getField($fieldName) {
		return Util::val($fieldName,$this->fields);
	}
	
	/**
	 * 
	 * @param type $fieldName
	 * @param type $fieldInfo
	 */
	function addField($fieldName,$fieldInfo) {
		$this->fields[$fieldName] = $fieldInfo;
	}
	
	/**
	 * 
	 * @param type $primaryKey
	 */
	function setPrimaryKey($primaryKey) {
		$this->primaryKey = $primaryKey;
	}
	
	/**
	 * 
	 * @return type
	 */
	function getPrimaryKey() {
		return $this->primaryKey;
	}
	
	/**
	 * 
	 * @param type $keyName
	 * @param type $keyType
	 * @param type $keyInfo
	 */
	function addKey($keyName, $keyType, $keyInfo) {
		$this->keys[$keyName]=array($keyType,$keyInfo);
	}
	
	/**
	 * 
	 * @param type $keyName
	 * @return type
	 */
	function getKey($keyName) {
		return Util::val($keyName,$this->keys);
	}
	/**
	 * 
	 * @param type $tableInfo
	 */
	function setTableInfo($tableInfo) {
		$this->tableInfo = $tableInfo;
	}
	/**
	 * 
	 * @return type
	 */
	function getTableInfo() {
		return $this->tableInfo;
	}
	
	function getCreateStatement() {
		return $this->createStatement;
	}

    /**
     * @param array $keys
     */
    public function setKey($keyName, $key)
    {
        $this->keys[$keyName] = $key;
    }

    /**
     * @param array $constraints
     */
    public function setConstraint($constraintName, $constraint)
    {
        $this->constraints[$constraintName] = $constraint;
    }


}
