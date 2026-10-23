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
use ClinicLE\System\SystemCreator\ApplicationSystemCreator;
use ClinicLE\Testing\AbstractBaseTest;

class HeadingCollatorTest extends AbstractBaseTest
{
    /** @var  HeadingCollator */
    private $SUT;
    private $dataFile;
    function setUp() {
        $creator = new ApplicationSystemCreator();
        $creator->create();
        $fileName = __DIR__."/../data/denorm-test.csv";
        $dataFile = new DelimitedDataFile($fileName);
        $dataFile->setHeaderRow(true);
        $this->dataFile = $dataFile;
        $this->dataFile->open();

        $this->SUT = new HeadingCollator($this->dataFile->getHeadings());
        $this->SUT->addPatientPrefix("basic_demog");
    }
    /**
     * @test
     */
    function it_should_determine_ignored_fields_based_on_headings() {
        $this->SUT->determinePatientFields();
        $expIgnoredFields = array(0 => 'basic_demog:identifier',
            1 => 'basic_demog:first_name',
            2 => 'basic_demog:last_name',
            3 => 'basic_demog:date_of_birth',
            4 => 'basic_demog:deceased');

        $this->assertEquals($expIgnoredFields,$this->SUT->getPatientFields());
    }

    /**
     * @test
     */
    function it_should_determine_unique_patient_rows() {
        $this->dataFile->open();
        $sections = $this->SUT->determineSections();
        $this->assertEquals(array("advcopd.annual"=>array("advcopd.annual.generic","advcopd.annual.expulmdis")),
            $sections);
    }

}