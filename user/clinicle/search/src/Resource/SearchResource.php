<?php

namespace ClinicLE\Search\Resource;
use ClinicLE\Common\Connection\StandardConnection;
use ClinicLE\Manage\Resource\ManageServices;
use ClinicLE\Migrate\MigrationHandler;
use ClinicLE\Module\AbstractResource;

use ClinicLE\Search\Command\BuildSearchCommand;
use ClinicLE\Search\Command\RunSearchCommand;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\System\Event\SystemEvents;
use ClinicLE\System\Service\CoreServices;
use Pimple\Container;
use ClinicLE\Install\Event\InstallEvents;
use ClinicLE\Install\Resource\InstallServices;
use ClinicLE\Search\Group\GroupSet;

use CLE;


/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:48
 */
class SearchResource extends AbstractResource
{
    function initResource(Container $services, EventDispatcher $events)
    {
        $services[SearchServices::GROUP_SET] = function($c) {
            $groupSet = new GroupSet();
            GroupSetup::setup($groupSet, $c[SearchServices::SEARCHABLE_PAGES]);
            return $groupSet;
        };
        $services[SearchServices::SEARCH_SESSION_DATA] = function($c) {
            $accessManager = $c[CoreServices::ACCESS_MANAGER];
            return new SearchSessionData($accessManager, $c[SearchServices::GROUP_SET]);
        };
        $services[SearchServices::SEARCH_REPOSITORY] = function($c) {
            $mainConn = CLE::svc(CoreServices::MAIN_CONNECTION);
            $sourceDbName = $mainConn->getDbName();
            $mainConn = new StandardConnection(CLE::sys()->getConfig()->getSection("SearchDatabase"));
            return new SearchRepository($mainConn,$c[CoreServices::ACCESS_MANAGER],$sourceDbName);
        };
        $services[SearchServices::SEARCHABLE_PAGES] = function($c) {
            return new SearchablePages(\CLE::sys()->getModules(), CLE::sys()->getAccessManager()->isAdmin());
        };
        $events->addListener(InstallEvents::BEFORE_DICTIONARIES,array($this,"onBeforeDictionaries"));
        $events->addListener(SystemEvents::AFTER_INIT,array($this,"onAfterInit"));
        $services[CoreServices::COMMAND_APP]->addCommands(
            array(
                new RunSearchCommand(),
                new BuildSearchCommand()
            )
        );
    }

    function onBeforeDictionaries($event) {
        $migHandler = new MigrationHandler(CLE::svc(SearchServices::SEARCH_REPOSITORY)->getConnection());
        $epiModule = CLE::sys()->getModule("clinicle/search");
        if(!$epiModule) {
            return;
        }
        $migHandler->migrateUsingFile($epiModule->getPath()."/sql/search.sql");
    }

    function onAfterInit($event) {
        $spagesSvc = CLE::svc(SearchServices::SEARCHABLE_PAGES);
        $spages = $spagesSvc->getAllSearchablePages();

        $pdemogPage = $spagesSvc->getModules()->getPageInModule("patient","demographics");
        $pdemogPage->setExpectedPermissions(array_merge($pdemogPage->getExpectedPermissions(),array("ident_search","anon_search")));

        foreach($spages as $spage) {
            $spage->setExpectedPermissions(array_merge($spage->getExpectedPermissions(),array("search")));
        }
    }
}