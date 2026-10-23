<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 23/05/17
 * Time: 12:26
 */

namespace ClinicLE\System\SystemCreator;


use ClinicLE\Module\ModuleCreator;
use ClinicLE\Module\ModuleMap\ModuleMap;
use ClinicLE\Module\ModuleSetup\ModuleSetup;
use ClinicLE\System\Environment\AbstractEnvironment;
use ClinicLE\System\Service\Services;
use Symfony\Component\Finder\Finder;

abstract class AbstractSystemCreator
{
    function create() {
        $env = new \ClinicLE\System\Environment\StandardEnvironment(getenv('ADD_PATH'), getenv('ADD_PROFILE'));

        $init = new \ClinicLE\System\Initialiser($env);
        $init->setErrorsHandled(true);
        $sys = \CLE::init($init);
        $init->initSystem($sys);
        return $sys;
    }
    protected function createModuleMap(AbstractEnvironment $env) {
        return new ModuleMap(new Finder(),$env->getLiveModulePath());
    }
}