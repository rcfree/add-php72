<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 14:59
 */

namespace ClinicLE\Search\Util;


class DelimitedDataWriter
{
    private $fileName;
    function __construct($fileName) {
        $this->fileName = $fileName;
    }

    function write(DataFrame $dataFrame) {
        $file = fopen($this->fileName,"w");
        fputcsv($file,$dataFrame->getColumns());
        foreach($dataFrame->getRows() as $row) {
            fputcsv($file,$row);
        }
        fclose($file);
    }
}