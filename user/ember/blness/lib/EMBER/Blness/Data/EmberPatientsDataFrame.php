<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 13:22
 */

namespace EMBER\Blness\Data;
use ClinicLE\Common\Util;

use ClinicLE\DataFrame\MatrixDataFrame;
use ClinicLE\Module\Module;

class EmberPatientsDataFrame extends MatrixDataFrame
{
    function __construct(EmberPatientExtractorParams $params) {
        $this->params = $params;
    }
    function addRow($row)
    {
        $diagnosis = Util::unserialize($row['Diagnosis']);
        if(is_array($diagnosis)) {
            $row['Diagnosis'] = implode(", ",array_values($diagnosis));
        }

        parent::addRow($row);
    }

}