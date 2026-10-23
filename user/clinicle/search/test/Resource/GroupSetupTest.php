<?php
namespace ClinicLE\Test\Search\Resource;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Resource\GroupSetup;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:28
 */
class FilterGroupSetupTest extends \ClinicLE\Testing\AbstractBaseTest
{
    function setUp() {
        $this->filterGroups = new GroupSet();
        $this->SUT = new GroupSetup($this->filterGroups);
    }

    /**
     * @test
     */
    function filter_groups_setup_correctly_with_types() {
        $this->SUT->init();
    }
}