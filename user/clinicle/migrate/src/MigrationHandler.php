<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Migrate;
use ClinicLE\Common\Connection\AbstractConnection;
use ClinicLE\Common\Log;
/**
 * Description of SqlFileMigration
 *
 * @author rob
 */
class MigrationHandler {
	private $sqlParser;
	function __construct(AbstractConnection $connection) {
		$this->connection = $connection;
		$this->sqlParser = new SqlParser();
	}
	
	function migrateUsingFile($migrateFileName) {
		$dbSchema = new Schema();
		$dbPopulator = new DatabaseSchemaPopulator($this->connection);
		
		$dbPopulator->populate($dbSchema,$this->sqlParser);
		
		$migrateFile = new \SplFileObject($migrateFileName,"r");
		$fileSchema = new Schema();
		$filePopulator = new FileSchemaPopulator($migrateFile);
		
		$filePopulator->populate($fileSchema,$this->sqlParser);
		
		$tableCompare = new TableCompare();
		$log = Log::logger($this);
		
		$migrationRequired=false;
		$this->connection->execute("SET FOREIGN_KEY_CHECKS = 0;");
		foreach($fileSchema->getTables() as $fileTableName => $fileTable) {
			if(!$dbSchema->hasTable($fileTableName)) {
				$log->info("Creating table '".$fileTableName."'");
				$this->connection->execute($fileTable->getCreateStatement());
				continue;
			}
			
			$dbTable = $dbSchema->getTable($fileTableName);
			
			$statement = $tableCompare->createModifyStatement($dbTable,$fileTable);
			
			if(!$statement) {
				continue;
			}
			$log->info("Updating table '".$fileTableName."'");
			$this->connection->execute($statement);
			$migrationRequired=true;
		}
		if(!$migrationRequired) {
			$ex = new \Exception();
			$log->info("No DB migration required using '$migrateFileName'");
		}
		$this->connection->execute("SET FOREIGN_KEY_CHECKS = 1;");
	}
}
