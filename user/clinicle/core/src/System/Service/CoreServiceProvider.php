<?php
namespace ClinicLE\System\Service;

use ClinicLE\Command\CommandApplication;
use ClinicLE\System\Factory\FileRegistryStorage;
use ClinicLE\System\Factory\RegistryStorage;
use Pimple\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ClinicLE\Service\Email\Emailer;
use ClinicLE\System\Cache\CacheManager;
use ClinicLE\Common\Factory\FactoryRegistry;
use ClinicLE\Data\RecordManager;
use ClinicLE\Data\Repository\TableEncounterRepository;
use ClinicLE\Data\Repository\EavEncounterRepository;
use ClinicLE\Data\Query\QueryRunner;
use ClinicLE\Data\Repository\SectionRepository;
use ClinicLE\Data\Repository\DomainRepository;
use ClinicLE\Common\Connection\StandardConnection;
use ClinicLE\Security\AccessManager;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\Data\Query\EncounterGroup;
use ADD;

class CoreServiceProvider implements \Pimple\ServiceProviderInterface {
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple An Container instance
     */
    public function register(Container $pimple)
    {
        $pimple[CoreServices::MAIN_CONNECTION]=function($c) {
            $dbConfig = ADD::sys()->getConfig()->getSection("Database");
            return new StandardConnection($dbConfig);
        };
        $pimple[CoreServices::ACCESS_MANAGER]=function($c) {
            return new AccessManager();
        };
        $pimple[CoreServices::DATA_MANAGER]=function($c) {
            return new RecordManager();
        };
        $pimple[CoreServices::DOMAIN_REPOSITORY]=function($c) {
            return new DomainRepository();
        };
        $pimple[CoreServices::SECTION_REPOSITORY]=function($c) {
            return new SectionRepository();
        };
        $pimple[CoreServices::ENCOUNTER_REPOSITORY]=function($c) {
            return new EavEncounterRepository($c[CoreServices::MAIN_CONNECTION]);
        };
		$pimple[CoreServices::ENCOUNTER_GROUP]=function($c) {
            return new EncounterGroup($c[CoreServices::ENCOUNTER_REPOSITORY]);
        };
		$pimple[CoreServices::QUERY_RUNNER]=function($c) {
            return new QueryRunner($c[CoreServices::MAIN_CONNECTION]);
        };
        $pimple[CoreServices::ENCOUNTER_MANAGER]=function($c) {
            return $c[CoreServices::DATA_MANAGER];
        };
        $pimple[CoreServices::DATABASE_MANAGER]=function($c) {
            return new \ADD_DatabaseManager();
        };

        $pimple[CoreServices::CACHE]=function($c) {
            return new \ClinicLE\System\Cache\MemoryCache();
        };

		$pimple[CoreServices::STATES]=function($c) {
			return new States();
		};

		$pimple[CoreServices::PROCESS]=function($c) {
			return new Process();
		};

		$pimple[CoreServices::FILESYSTEM]=function($c) {
			return new Filesystem();
		};

		$pimple[CoreServices::FILEFINDER]=$pimple->factory(function($c) {
			return Finder::create();
		});

		$pimple[CoreServices::EMAILER]=function($c) {
			return new Emailer();
		};

		$pimple[CoreServices::CONFIG]=\ADD::sys()->getConfig();

		$pimple[CoreServices::USER_PASSWORD_EMAILER]=$pimple->factory(function($c) {
			return new UserPasswordEmailer($c[CoreServices::EMAILER],$c[CoreServices::CONFIG]);
		});

		$pimple[CoreServices::CACHE_MANAGER]=function($c) {
			return new CacheManager($c[CoreServices::CACHE],\ADD::sys());
		};

		$pimple[CoreServices::MINIFIER]=function($c) {
			return new Minifier(\ADD::sys()->getEnvironment());
		};
		$pimple[CoreServices::FIELD_FUNCTIONS]=function($c) {
			return new FieldFunctions();
		};
        $pimple[CoreServices::MIGRATION_HANDLER]=function($c) {
            return new \ClinicLE\Migrate\MigrationHandler($c[CoreServices::MAIN_CONNECTION]);
		};
        $pimple[CoreServices::LOCALE]=function($c) {
            return new Locale();
        };
        $pimple[CoreServices::FACTORY_REGISTRY]=function($c) {
            $storage = new FileRegistryStorage();
            return $storage->createRegistry();
        };
        $pimple[CoreServices::COMMAND_APP]=function($c) {
            $app = new CommandApplication("ClinicLE management tool", "v1.0.0",\CLE::sys());
            /*$app->addCommands(array(
                new BuildCommand(),
                new InstallCommand(),
                new LoadCommand()
            ));
            */
            return $app;
        };
    }
}
?>
