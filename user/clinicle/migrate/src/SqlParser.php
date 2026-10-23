<?php

namespace ClinicLE\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ClinicLE\Common\Log;

class SqlParser {
    private function extractTableItems($tableFieldString) {
        $tableFields = explode(",",$tableFieldString);
        $extractedItems = array();
        $counter = 0;

        while($counter<count($tableFields)) {
            $line = $this->extractLineFromFields($tableFields,$counter);
            $counter+=count($line);
            $extractedItems[]=implode(",",$line);
        }
        return $extractedItems;
    }
    function extractLineFromFields($tableFields, $currCounter) {
        $line = array();
        while(true) {
            $currField = $tableFields[$currCounter];

            if(!isset($tableFields[$currCounter+1])) {
                $line[]=$currField;
                return $line;
            }
            $nextField = $tableFields[$currCounter+1];

            if(preg_match("/^'/",$nextField)) {
                $line[]=$currField;
                $currCounter++;
                continue;
            }
            if(preg_match("/^`/",$nextField)) {
                $line[]=$currField;
                $currCounter++;
                continue;
            }
            $line[]=$currField;
            break;

        }
        return $line;
    }
	function createTableFromSql($createStatement) {
		$table = new Table($createStatement);
		$tableMatches = array();
		$createStatement = preg_replace('/\n/',' ',$createStatement);

        $match = preg_match('/^CREATE TABLE(.*?)\((.*)\)(.*)$/',$createStatement, $tableMatches);
		if(!$match) {
			return;
		}
        $tableName = $tableMatches[1];
		$table->setTableName($this->removeBackTicks($tableName));

		$tableInfo = $tableMatches[3];
		$table->setTableInfo(trim($tableInfo,' ;'));

        $tableItems = $this->extractTableItems($tableMatches[2]);
        foreach($tableItems as $tableItem) {
            $this->addTableItem($tableItem, $table);
		}
		
		return $table;
	}
	function removeBackTicks($text) {
		return trim($text," `");
	}
	function addTableItem($tableItem, Table $table) {
        $tableItem = trim($tableItem);

        $matches = array();

        if(preg_match("/^(?i)PRIMARY KEY(?-i) (.+)$/",$tableItem ,$matches)) {
            $table->setPrimaryKey($this->removeBackTicks($matches[1]));
            return;
        }

        if(preg_match("/^((?i)KEY(?-i))(.+) (.+)$/",$tableItem ,$matches)) {
            $table->addKey($this->removeBackTicks($matches[2]),$matches[1],$matches[3]);
            return;
        }

        if(preg_match("/^((?i)UNIQUE KEY(?-i))(.+) (.+)$/",$tableItem ,$matches)) {
            $table->addKey($this->removeBackTicks($matches[2]),$matches[1],$matches[3]);
            return;
        }

        if(preg_match("/^((?i)CONSTRAINT(?-i)) (.+?) (.+)$/",$tableItem ,$matches)) {
            $table->addKey($this->removeBackTicks($matches[2]),$matches[1],$matches[3]);
            return;
        }
        if(preg_match("/^(.+?) (.+)/", $tableItem , $matches)) {
            $table->addField($this->removeBackTicks($matches[1]),$matches[2]);
        }

    }

}