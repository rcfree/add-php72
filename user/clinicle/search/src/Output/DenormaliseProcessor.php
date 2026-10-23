<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 15:20
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Common\Util;
use ClinicLE\DataFile\BatchReader;
use ClinicLE\DataFile\DelimitedDataFile;

class DenormaliseProcessor
{

    function denormalise(DelimitedDataFile $dataFile, HeadingCollator $headingCollator, RowProcessor $rowProcessor) {
        $headingCollator->collate();
        $rowCount = 0;

        while($row = $dataFile->nextRow()) {

            $allowedRow = $row;
            //foreach($this->ignoredFields as $ignoreField) {
            //    unset($allowedRow[$ignoreField]);
            //}
            if($rowProcessor->isDifferentPatient($row)) {
                \CLE::info("write row:",$rowProcessor->getDenormalisedRow());
            }
            $rowProcessor->processRow($row);



            $rowCount++;
        }
    }

}