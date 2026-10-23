<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Migrate;
use ClinicLE\Common\Util;

class Schema {
	private $tables = array();
	
	/**
	 * 
	 * @param \ClinicLE\Migrate\Table $table
	 */
	function addTable(Table $table) {
		$this->tableNames[]=$table->getTableName();
		$this->tables[$table->getTableName()] = $table;
	}
	
	/**
	 * 
	 * @param type $tableName
	 * @return bool
	 */
	function hasTable($tableName) {
		return isset($this->tables[$tableName]);
	}
	
	/**
	 * 
	 * @param string $tableName
	 * @return \ClinicLE\Migrate\Table
	 */
	function getTable($tableName) {
		return Util::val($tableName,$this->tables);
	}
	
	/**
	 * 
	 * @return array
	 */
	function getTables() {
		return $this->tables;
	}

}