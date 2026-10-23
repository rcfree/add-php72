<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 13:22
 */

namespace ClinicLE\DataFrame;


class MatrixDataFrame extends DataFrame
{

    function toArray() {
        $array = array();
        $record = array();
        foreach($this->getColumns() as $column) {
            $record[]=array("head"=>true,"label"=>$column);
        }
        $array[]=$record;
        foreach($this->getRows() as $row) {
            $record = array();
            foreach($this->getColumns() as $column) {
                $record[]=array("label"=>$row[$this->getColumnPos($column)]);
            }
            $array[]=$record;
        }
        return $array;
    }
}