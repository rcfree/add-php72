<?php
namespace ClinicLE\Search\Event;
use ClinicLE\Search\Util\DataFrame;
use Symfony\Component\EventDispatcher\Event;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/11/17
 * Time: 10:02
 */

class ExportEvent extends Event
{
    function __construct(DataFrame $dataFrame) {
        $this->dataFrame = $dataFrame;
    }

    /**
     * @return DataFrame
     */
    public function getDataFrame()
    {
        return $this->dataFrame;
    }


}