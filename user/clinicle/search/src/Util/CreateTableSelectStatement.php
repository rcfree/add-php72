<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 11:02
 */

namespace ClinicLE\Search\Util;


use ClinicLE\Common\Statement\SelectStatement;

class CreateTableSelectStatement extends SelectStatement
{
    private $tableName;

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }


    public function generateSql()
    {
        return "create table ".$this->getTableName()." as ".parent::generateSql();
    }
}