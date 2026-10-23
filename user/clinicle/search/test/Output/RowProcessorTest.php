<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 15:11
 */

namespace ClinicLE\Test\Search\Output;


use ClinicLE\DataFile\DelimitedDataFile;
use ClinicLE\Search\Output\DenormaliseProcessor;
use ClinicLE\Search\Output\HeadingCollator;
use ClinicLE\Search\Output\RowProcessor;
use ClinicLE\System\SystemCreator\ApplicationSystemCreator;
use ClinicLE\Testing\AbstractBaseTest;

class RowProcessorTest extends AbstractBaseTest
{
    /** @var  RowProcessor */
    private $SUT;

    function setUp() {
        $creator = new ApplicationSystemCreator();
        $creator->create();
        $this->dataFile = new DelimitedDataFile(__DIR__."/../data/denorm-test.csv");
        $this->dataFile->setHeaderRow(true);
        $this->dataFile->open();
        $headingCollator = new HeadingCollator($this->dataFile->getHeadings());
        $headingCollator->addPatientPrefix("basic_demog");

        $this->SUT = new RowProcessor($headingCollator);

    }

    /**
     * @test
     */
    function it_should_map_encounters_by_section_id() {
        $this->SUT->processRow($this->dataFile->nextRow());
        $this->SUT->processRow($this->dataFile->nextRow());
        $this->SUT->processRow($this->dataFile->nextRow());
        \CLE::info("headings",count($this->SUT->getDenormalisedHeadings()),count($this->SUT->getDenormalisedRow()));
    }
}