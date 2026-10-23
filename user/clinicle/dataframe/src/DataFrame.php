<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 13:22
 */

namespace ClinicLE\DataFrame;


class DataFrame
{
    private $columns=array();
    private $columnCount=0;
    private $rowCount=0;
    private $rows= array();
    function __construct($data=array()) {
        $this->fromArray($data);
    }
    function getColumns() {
        return array_keys($this->columns);
    }
    function getColumnPos($column) {
        return $this->columns[$column];
    }
    function fromArray($data) {
        foreach($data as $row) {
            $this->addRow($row);
        }
    }
    function getRows() {
        return $this->rows;
    }
    function addRow($row) {
        foreach($row as $key => $value) {
            if (!isset($this->columns[$key])) {
                $this->columns[$key] = $this->columnCount;
                $this->columnCount++;
            }
            $this->rows[$this->rowCount][$this->getColumnPos($key)] = $value;
        }
        $this->rowCount++;
    }
    function toArray() {
        $array = array();


        foreach($this->getRows() as $row) {
            $record = array();
            foreach($this->getColumns() as $column) {
                $record[$column]=$row[$this->getColumnPos($column)];
            }
            $array[]=$record;
        }
        return $array;
    }
}