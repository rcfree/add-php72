<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Migrate;

class FileSchemaPopulator extends AbstractSchemaPopulator {
	private $file;
	function __construct(\SplFileObject $file) {
		$this->file = $file;
	}
	function populate(Schema $schema, SqlParser $sqlParser) {
		$statement = array();
		foreach($this->file as $line) {
            if(preg_match("/^--|\/\*|#/",$line)) {
                continue;
            }
			if(strlen(trim($line))>0) {
				$statement[]=$line;
			}

			if(preg_match("/;$/",$line)) {
				$statements[]=implode("\n",$statement);
				$statement = array();
			}

		}
		
		if(count($statement)>0) {
			$statements[]=implode("\n",$statement);
		}
		foreach($statements as $statement) {
			$structure = $sqlParser->createTableFromSql($statement);
			if(!$structure) {
				continue;
			}
			$schema->addTable($structure);
		}
	}
}