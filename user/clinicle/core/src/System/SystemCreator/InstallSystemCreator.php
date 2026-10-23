<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/06/17
 * Time: 10:47
 */

namespace ClinicLE\System\SystemCreator;


use ClinicLE\Module\ModuleCollection;
use ClinicLE\Module\ModuleFactory\ModuleBuilder;
use ClinicLE\Module\ModuleMap\ModuleMap;
use ClinicLE\Module\ModuleMap\SingleModuleMap;
use ClinicLE\Module\ResourceSetup;
use ClinicLE\System\Environment\AbstractEnvironment;
use ClinicLE\System\SystemCreator\AbstractSystemCreator;
use Symfony\Component\Finder\Finder;

class InstallSystemCreator extends AbstractSystemCreator
{
    private $singleModuleName;
    function __construct($singleModuleName) {
        $this->singleModuleName = $singleModuleName;
    }

    protected function createModuleMap(AbstractEnvironment $env) {
        $finder = new Finder();
        if($this->singleModuleName) {
            $moduleMap = new SingleModuleMap($finder,$env->getLiveModulePath());
            $moduleMap->setSingleModulePath($env->getSourceModulePath());
            $moduleMap->setSingleModuleName($this->singleModuleName);
            return $moduleMap;
        }
        else {
            return new ModuleMap($finder, $env->getLiveModulePath());
        }
    }
}