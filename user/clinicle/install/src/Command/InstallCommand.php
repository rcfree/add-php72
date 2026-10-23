<?php

namespace ClinicLE\Install\Command;

use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Command\AbstractCommand;
use ClinicLE\Install\ModuleInstall;
use ADD;

/**
 * Set up a module's forms and dictionaries from its installed definition.
 * Module code must already exist under work/; this command never downloads,
 * copies, symlinks, or removes module code.
 */
class InstallCommand extends AbstractCommand
{
    function getOptsList() {
        return "m::pr";
    }

    function getDescription() {
        return "Set up module forms and dictionaries";
    }

    function runCommand(AbstractConfig $options) {
        $modName = $options->get("m") or die("FATAL: Module (-m) not specified\n");
        $deleteMissing = $options->has("p");
        $add = ADD::sys();

        $setup = new \ClinicLE\Module\SingleModuleSetup($add);
        $setup->setSingleModuleName($modName);
        $setup->setSingleModulePath($add->getWorkModulePath());
        $setup->setIgnoreSubModule(true);
        $setup->loadModules();

        $events = $add->getEventDispatcher();
        $events->dispatch(\ClinicLE\Install\Event\InstallEvents::BEFORE_RUN);

        $module = $add->getModules()->get($modName);
        if (!$module) {
            throw new \Exception("Module '$modName' not found under ".$add->getWorkModulePath());
        }

        if ($options->has('r')) {
            echo "Module '$modName' loaded from ".$module->getPath()."\n";
            return;
        }

        $moduleInstall = new ModuleInstall();
        $moduleInstall->installForms($module, $deleteMissing);
    }
}
