<?php

/**
 * Compatibility adapter for the removed legacy installer.
 *
 * Module code is expected to already exist under work/. This class never
 * downloads, copies, symlinks, or removes module code.
 */
class ADD_Manage_Install
{
    function getOptsList()
    {
        return "m::prn";
    }

    function getDescription()
    {
        return "Install module forms and dictionaries";
    }

    function runCommand($options)
    {
        $this->manage($options);
    }

    function manage($options)
    {
        $isArray = is_array($options);
        $get = function ($key) use ($options, $isArray) {
            return $isArray
                ? (isset($options[$key]) ? $options[$key] : null)
                : $options->get($key);
        };
        $has = function ($key) use ($options, $isArray) {
            return $isArray ? isset($options[$key]) : $options->has($key);
        };

        $modName = $get('m');
        if (!$modName) {
            throw new ADD_SystemException("Module (-m) not specified");
        }

        $add = ADD::sys();
        $setup = new \ClinicLE\Module\SingleModuleSetup($add);
        $setup->setSingleModuleName($modName);
        $setup->setSingleModulePath($add->getWorkModulePath());
        $setup->setIgnoreSubModule(true);
        $setup->loadModules();

        $module = $add->getModules()->get($modName);
        if (!$module) {
            throw new ADD_SystemException(
                "Module '$modName' not found under " . $add->getWorkModulePath()
            );
        }

        if ($has('r')) {
            echo "Module '$modName' loaded from " . $module->getPath() . "\n";
            return;
        }

        $moduleInstall = new \ClinicLE\Install\ModuleInstall();
        if ($has('n')) {
            $moduleInstall->setDryRun(true);
        }
        $moduleInstall->installForms($module, $has('p'));
    }
}
