<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Command;
use ADD;
use ClinicLE\System\Service\CoreServices;
use Logger;
use LoggerLevel;
use ADD_Util;
use Exception;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use ClinicLE\System\Event\SystemEvents;


class CommandRunner {
	private $environment;
	public function __construct($environment) {
		$this->environment = $environment;
	}
	public function runCommand() {
		$env = $this->environment;
		$path = $env->getPath();

		chdir($path);
		set_error_handler(function($errno, $errstr) {
			$path = getenv('ADD_PATH');
			if (empty($path))
				die("ERROR: No 'ADD_PATH' set");

			throw new \Exception($errstr);
		});

        $options = $env->getOptions();
        
        $envProfile = isset($options['e']) ? $options['e'] : $env->getProfile();

		$init = new \ClinicLE\System\Initialiser($this->environment);
		
		$system = ADD::init($init);
		$system->initServiceContainer();
		
		$moduleSetup = new \ClinicLE\Module\SingleModuleSetup($system);
		$moduleSetup->setSingleModuleName("clinicle/manage");
		$moduleSetup->setSingleModulePath($system->getWorkModulePath());
		//$moduleSetup->setLoadSingleOnly(true);
		$map = $moduleSetup->extractModuleMap($system->getWorkModulePath());
		
		$moduleSetup->loadModules();
        $system->getEvents()->dispatch(SystemEvents::COMMAND_REGISTER);
        \CLE::log($this);
        $root = Logger::getRootLogger();
        $root->setLevel(isset($options['v']) ? LoggerLevel::getLevelDebug() : LoggerLevel::getLevelInfo());

        $log = ADD::log("clinicle");
        $app = $system->getService(CoreServices::COMMAND_APP);

        $input = new ArgvInput();
        $output = new ConsoleOutput();

        if(count($app->all())===0) {
            $output->writeln("Attempting to run legacy comand");
            $this->runLegacyCommand($system, $env, $options);
            return;
        }
        if($app->has($env->getManageAction())===false) {
            $output->writeln("Attempting to run legacy comand");
            $this->runLegacyCommand($system, $env, $options);
            return;
        }


        $app->setCatchExceptions(true);
        $app->setCommandRunner($this);

        $app->run($input, $output);
    }

    function auditManageRun($manAction, $cmdArgs, $failure = false, $message = null) {
        return;
	    ADD::db('audit')->createAudit(array(
            "record_type" => "manage",
            "action" => $manAction,
            "action_info" => json_encode(
                array(
                    "status" => $failure ? "failed" : "success",
                    "message" => $message,
                    "cmd_args" => $cmdArgs
                )
            )
        ));
    }

    function createDirectory($description, $path) {
        if (file_exists($path)) {
            echo "Existing $description directory ($path)\n";
            return;
        }
        echo "Creating $description directory ($path)\n";

        if (!mkdir($path, 0777))
            throw new Exception("Unable to create path '$path'");
    }
    function getEnvironment() {
        return $this->environment;
    }

    /**
     * @param $add
     * @param $env
     * @param $options
     */
    public function runLegacyCommand($add, $env, $options) {
        $log = ADD::log("clinicle");
        $manClass = null;

        try {
            $log->info("CONFIG: " . $add->getPath() . "/config/" . $add->getProfile());
            if (!$add->getConfig()->hasSection('Database'))
                die("FATAL: Uh oh! Database section not found in profile config\n");
            $log->info("DATABASE: " . $add->getConfig()->getSection('Database')->getSetting('dsn'));
            //$ml = new ADD_ModuleLoad($add->config('inplace') ? $add->getSourceModulePath() : $add->getWorkModulePath());
            $manClass = ADD::sys()->newFactoryInstance(\ClinicLE\Manage\Resource\ManageFactories::COMMAND, ADD_Util::convertToPascal($env->getManageAction()), array($this));
            //ADD_Util::loadClass("ADD_Manage_" . ADD_Util::convertToPascal($env->getManageAction()),$this);
        }
        catch (Exception $ex) {
            $log->fatal("Command '" . $env->getManageAction() . "' is not available or could not be loaded (" . $ex->getMessage() . "\nSTACKTRACE:" . $ex->getTraceAsString());
            if (method_exists($manClass, "runAtEnd")) {
                $manClass->runAtEnd($ex);
            }
            exit(1);
        }
        $optsList = $manClass->getOptsList();

        try {
            $log->info("Loaded command '" . $env->getManageAction() . "' with options:" . json_encode($options));
            if (is_a($manClass, "ADD_ManageInterface")) {
                $returnVal = $manClass->manage($options);
            } else {
                $returnVal = $manClass->runCommand(new \ClinicLE\Common\Config\ArrayConfig($options));
            }
        }
        catch (ADD_CommandReturnValueException $ex) {
            if (!isset($options['q']))
                $log->fatal($manClass->getDescription() . " (" . $env->getManageAction() . ") FAILED: " . $ex->getMessage() . "\nEXCEPTION:" . get_class($ex) . "\nSTACKTRACE:" . $ex->getTraceAsString());
            $log->fatal("Command output:" . $ex->getOutputAsString());
            if (method_exists($manClass, "runAtEnd")) {
                $manClass->runAtEnd($ex);
            }
            $this->auditManageRun($env->getManageAction(), $options, true, $ex->getMessage());

            exit(1);
        }
        catch (Exception $ex) {
            if (!isset($options['q']))
                $log->fatal($manClass->getDescription() . " (" . $env->getManageAction() . ") FAILED: " . $ex->getMessage() . "\nEXCEPTION:" . get_class($ex) . "\nSTACKTRACE:" . $ex->getTraceAsString());
            if (method_exists($manClass, "runAtEnd")) {
                $manClass->runAtEnd($ex);
            }
            $this->auditManageRun($env->getManageAction(), $options, true, $ex->getMessage());
            exit(1);
        }
        if (method_exists($manClass, "runAtEnd")) {
            $manClass->runAtEnd(null);
        }
        $log->info($manClass->getDescription() . " ran successfully");
        $this->auditManageRun($env->getManageAction(), $options);
        $log->complete();
        echo "\n";
    }
}
