<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Migrate;
use ClinicLE\Common\Connection\AbstractConnection;

class DatabaseSchemaPopulator extends AbstractSchemaPopulator {
	private $connection;
	function __construct(AbstractConnection $connection) {
		$this->connection = $connection;
	}
	function populate(Schema $schema, SqlParser $sqlParser) {
		$tableList = $this->connection->fetchAll("SELECT table_name
	    FROM information_schema.tables 
	    WHERE table_schema = ?",$this->connection->getDbName());
		
		foreach($tableList as $tableItem) {
			$tableName = $tableItem['table_name'];
			$createTable = $this->connection->fetchFirst("show create table `".$tableName."`");

			if(!isset($createTable['Create Table'])) {
			    continue;
            }
			$table = $sqlParser->createTableFromSql($createTable['Create Table']);
			$schema->addTable($table);
		}
	}
}