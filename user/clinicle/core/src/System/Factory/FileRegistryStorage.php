<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 30/09/2016
 * Time: 12:22
 */

namespace ClinicLE\System\Factory;


use ClinicLE\Common\Factory\FactoryRegistry;
use ClinicLE\Common\Factory\RegistryStorageInterface;

class FileRegistryStorage implements RegistryStorageInterface
{

    public function getRegistryFileName() {
        return \CLE::sys()->getEnvironment()->getConfigPath()."/registry";
    }
    public function storeRegistry(FactoryRegistry $registry) {
        if(!$registry->hasRegistryChanged()) {
            return;
        }
        $registryFileName = $this->getRegistryFileName();

        $registryFh = fopen($registryFileName,"w+");
        if(!flock($registryFh, LOCK_EX)) {
            usleep(100000); #wait 100ms
            return $this->storeRegistry($registry);
        }

        fwrite($registryFh,serialize($registry->getCompiledClassNames()));
        flock($registryFh, LOCK_UN);
        fclose($registryFh);
    }
    public function createRegistry() {
        $registry = new FactoryRegistry($this);

        $registry->addFactory(SystemFactories::PAGE, "ClinicLE\System\Factory\PageFactory")
            ->addFactory(SystemFactories::RESOURCE, "ClinicLE\System\Factory\ResourceFactory")
            ->addFactory(SystemFactories::COMPONENT, "ClinicLE\System\Factory\ComponentFactory")
            ->addFactory(SystemFactories::DB_FIELD, "ClinicLE\System\Factory\DbFieldFactory")
            ->addFactory(SystemFactories::ROW_FIELD, "ClinicLE\System\Factory\RowFieldFactory")
            ->addFactory(SystemFactories::REPORT_FIELD, "ClinicLE\System\Factory\ReportFieldFactory")
            ->addFactory(SystemFactories::COMMAND, "ClinicLE\System\Factory\CommandFactory");
        $registry->addNamespace("ClinicLE\Install");

        $registryFileName = $this->getRegistryFileName();

        $this->populateRegistryClassNames($registryFileName, $registry);
        return $registry;
    }
    private function populateRegistryClassNames($registryFileName, FactoryRegistry $registry) {
        if(\CLE::sys()->getEnvironment()->useCleanRegistry()) {
            return;
        }
        if(!file_exists($registryFileName)) {
            return;
        }

        $registryFh = fopen($registryFileName,"r+");
        if(!flock($registryFh, LOCK_EX | LOCK_NB)) {
            usleep(100000);
            return $this->populateRegistryClassNames($registryFileName,$registry);
        }
        $registryClassData = fread($registryFh, filesize($registryFileName));
        $registryClasses = unserialize($registryClassData);
        $registry->setCompiledClassNames($registryClasses);
        flock($registryFh, LOCK_UN);
        fclose($registryFh);
    }
}