<?php

namespace ClinicLE\Test\Search\Component;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\Resource\SearchSessionData;
use ClinicLE\Security\Access;
use ClinicLE\Security\AccessManager;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\System\Session\SessionStorage;
use ClinicLE\Testing\AbstractBaseTest;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 20/03/2017
 * Time: 20:58
 */
class SearchDefinitionHandlerComponentTest extends AbstractBaseTest
{
    function setUp() {
        $settings = array("testpage"=>array("components"=>"SearchDefinitionHandler Form_File Breadcrumb","form"=>"search-def.ftx"));

        $this->initMockSystem();
        $this->initTestServices();

        $this->addMockService(CoreServices::SESSION,SessionStorage::class,true);
        $this->addMockService(SearchServices::GROUP_SET,GroupSet::class,true);
        $services = \CLE::sys()->getServiceContainer();
        $services[SearchServices::SEARCH_SESSION_DATA]=function($c) {
            return new SearchSessionData($c[CoreServices::ACCESS_MANAGER],$c[SearchServices::GROUP_SET]);
        };

        \CLE::svc(CoreServices::FACTORY_REGISTRY)->addNamespace("ClinicLE\\Search");

        $this->initTestModule("clinicle/search","/local/add_modules/clinicle/search");

        //$this->page = new \ADD_Page("test",$settings);
        //$this->page->init();
        //$this->accessManager = $this->addMockService(CoreServices::ACCESS_MANAGER,AccessManager::class,true);
    }

    /**
     * @test
     */
    function create_page_content() {
        \CLE::sys()->setParam("action","create");
        $page = \CLE::sys()->getPageFromPim("search:definition");
        $page->setup();

        $page->createContent();
    }
}