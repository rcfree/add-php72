<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Migrate;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\Common\Log;
use ClinicLE\Common\Util;

class TableCompare {
	private $includeDropFields = false;
    private $includeModifyFields = false;
    private $includeDropKeys = false;
    private $includeDropPrimaryKey = false;

    /**
     * @param boolean $includeDropPrimaryKey
     */
    public function setIncludeDropPrimaryKey($includeDropPrimaryKey)
    {
        $this->includeDropPrimaryKey = $includeDropPrimaryKey;
    }

	function createModifyStatement(Table $original, $new) {
		if(is_array($new)) {
		    return $this->createModifyStatementFromArray($original, $new);
        }
        return $this->createModifyStatementFromTable($original, $new);
	}
	function createModifyStatementFromArray(Table $original, $items) {
	    $sqlParser = new SqlParser();
	    $new = new Table();
        $new->setTableName($original->getTableName());
        foreach($items as $item) {
            $sqlParser->addTableItem($item,$new);
        }
        $this->setIncludeDropFields(false);

        return $this->createModifyStatementFromTable($original, $new);
    }

    /**
     * @param boolean $includeDropKeys
     */
    public function setIncludeDropKeys($includeDropKeys)
    {
        $this->includeDropKeys = $includeDropKeys;
    }

    function setIncludeDropFields($dropFields) {
		$this->includeDropFields = $dropFields;
	}
	function setIncludeModifyFields($modifyFields) {
	    $this->includeModifyFields = $modifyFields;
    }

    /**
     * @param Table $original
     * @param $new
     * @return null|string
     */
    public function createModifyStatementFromTable(Table $original, $new)
    {
        $columnChanges = $this->identifyColumnChanges($original, $new);
        $keyChanges = $this->identifyKeyChanges($original, $new);

        $statements = array_merge($columnChanges, $keyChanges);

        if (count($statements) == 0) {
            return null;
        }
        return "alter table `" . $new->getTableName() . "` " . implode(", ", $statements);
    }

    /**
     * @param Table $original
     * @param $new
     * @return array
     */
    public function identifyColumnChanges(Table $original, $new)
    {
        $originalFields = array_keys($original->getFields());
        $newFields = array_keys($new->getFields());

        $addedFields = array_diff($newFields, $originalFields);

        $statements = array();

        if ($this->includeDropFields) {
            $removedFields = array_diff($originalFields, $newFields);
            foreach ($removedFields as $removedField) {
                $statements[] = "drop column `" . $removedField . "`";
            }
        }

        foreach ($addedFields as $addedField) {
            $statements[] = "add column `" . $addedField . "` " . $new->getField($addedField);
        }
        foreach ($new->getFields() as $fieldName => $newFieldInfo) {
            if (in_array($fieldName, $addedFields)) {
                continue;
            }
            $oldFieldInfo = $original->getField($fieldName);
            if ($oldFieldInfo == $newFieldInfo) {
                continue;
            }
            if ($this->includeModifyFields) {
                $statements[] = "modify column `" . $fieldName . "` " . $newFieldInfo;
            }
        }
        return $statements;
    }

    private function createKeyStatements($originalKeys, $newKeys, $keyType=null) {
        $statements = array();

        $originalKeyNames = array_keys($originalKeys);
        $newKeyNames = array_keys($newKeys);

        if ($this->includeDropKeys) {
            $removedKeys = array_diff($originalKeyNames, $newKeyNames);
            foreach ($removedKeys as $removedKey) {
                $originalKeyDef = $originalKeys[$removedKey];
                $statements[] = "drop ".($keyType ? : $originalKeyDef[0])." `$removedKey`";
            }
        }

        $addedKeys = array_diff($newKeyNames, $originalKeyNames);
        foreach ($addedKeys as $addedKey) {
            $newKeyDef = $newKeys[$addedKey];

            $statements[] = "add ".($keyType ? :$newKeyDef[0])." `$addedKey` ".$newKeyDef[1];
        }
        /*
        foreach ($newKeys as $keyName => $newKeyDef) {
            if (in_array($keyName, $addedKeys)) {
                continue;
            }
            $oldKeyDef = $originalKeys[$keyName];
            if ($oldKeyDef == $newKeyDef) {
                continue;
            }

            $statements[] = "modify ".($keyType ? : $newKeyDef[0])." `$keyName` ".$newKeyDef[1];
        }
        */
        return $statements;
    }

    private function createPrimaryKeyStatement($originalKeyDef, $newKeyDef) {

        if(!$newKeyDef && !$originalKeyDef) {
            return null;
        }
        if(!$newKeyDef && $originalKeyDef) {
            if($this->includeDropPrimaryKey) {
                return "drop PRIMARY KEY $newKeyDef";
            }
            else {
                return null;
            }
        }
        if (!$originalKeyDef && $newKeyDef) {
            return "add PRIMARY KEY $newKeyDef";
        }
        if ($originalKeyDef != $newKeyDef) {
            return "modify PRIMARY KEY $newKeyDef";
        }

        return null;
    }
    /**
     * @param Table $original
     * @param $new
     * @return array
     */
    public function identifyKeyChanges(Table $original, $new)
    {
        $statements = $this->createKeyStatements($original->getKeys(),$new->getKeys());
        $statement = $this->createPrimaryKeyStatement($original->getPrimaryKey(),
            $new->getPrimaryKey());

        if($statement) {
            array_unshift($statements, $statement);
        }

        return $statements;
    }

}