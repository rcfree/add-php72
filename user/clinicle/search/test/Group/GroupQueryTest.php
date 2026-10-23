<?php
namespace ClinicLE\Test\Search\Resource;
use ClinicLE\Search\Group\EncounterGroup;
use ClinicLE\Search\Group\GroupQuery;
use ClinicLE\Search\Group\GroupQueryRunner;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Resource\GroupSetup;
use ClinicLE\System\Service\CoreServices;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:28
 */
class GroupQueryTest extends \ClinicLE\Testing\AbstractBaseTest
{
    private $SUT;
    function setUp() {
        $sys = $this->initMockSystem();

        $this->mainConn = $this->addMockService(CoreServices::MAIN_CONNECTION,"\ClinicLE\Common\Connection\StandardConnection",true);
        $groups = new GroupSet();
        $groupSetup = new GroupSetup($groups);

        $groupSetup->init();

        $this->SUT1 = new GroupQuery($groups->getGroup("patients"));
        $this->SUT2 = new GroupQuery($groups->getGroup("history-condition-summary"));
    }

    /**
     * @test
     */
    function filter_groups_setup_correctly_with_types()
    {
        $this->SUT1->addFilter("age", "ge", 18);
        $this->SUT1->addFilter("age", "le", 65);

        $this->SUT2->addFilter("overall_condition","eq",array("H","1"));

        $runner = new GroupQueryRunner();
        $runner->addGroupQuery($this->SUT1);
        $runner->addGroupQuery($this->SUT2);
        $runner->run();
    }

}