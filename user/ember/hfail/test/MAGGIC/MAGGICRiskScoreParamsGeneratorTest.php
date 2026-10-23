<?php
namespace EMBER\HFail\Test\MAGGIC;

use ClinicLE\Data\PageFieldSearch\PageFieldSearchCreator;
use ClinicLE\System\SystemCreator\ApplicationSystemCreator;
use EMBER\HFail\MAGGIC\MAGGICRiskScore;
use EMBER\HFail\MAGGIC\MAGGICRiskScoreParamsGenerator;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 27/10/17
 * Time: 12:24
 */

class MAGGICRiskScoreParamsGeneratorTest extends \ClinicLE\Testing\AbstractBaseTest
{
    function setUp() {
        //$this->system = $this->initMockSystem();
        $creator = new ApplicationSystemCreator();
        $this->system = $creator->create();
        //$this->initTestServices();
        //$this->initTestModule("ember/blness",array());

    }

    /**
     * @test
     */
    function it_should_work() {
        $this->markTestSkipped("Requires specific setup");
        $queryParams = array("eid"=>669589,"pid"=>6817);
        $creator = new PageFieldSearchCreator($this->system->getModules(),$queryParams,"blness:ascreening","calculate MAGGIC score");
        $this->SUT = new MAGGICRiskScoreParamsGenerator($creator);
        $params = $this->SUT->createParams();
        if($creator->hasWarnings()) {
            \CLE::web()->addWarning($creator->getWarnings());
            return;
        }
        $score = new MAGGICRiskScore();

        \CLE::info("maggic score:".$score->calculateScore($params));
    }
}