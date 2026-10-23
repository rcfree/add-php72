<?php
/**
 * Description of ADD_Manage_IntCheck
 *
 * @author rcf8
 */
namespace ClinicLE\Manage\Command;
use ClinicLE\Common\Exception\DirectoryNotFoundException;
use ClinicLE\Common\Exception\FileNotFoundException;
use ClinicLE\Common\Util;
use CLE;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use ClinicLE\System\Util\Execute;
use Symfony\Component\Filesystem\Filesystem;

class UnitTestCommand extends Command
{
    function getDescription() {
        return "Unit test module or library";
    }

    protected function configure() {
        $this->setName("unit-test");
        $this->setDefinition(array(
            new InputArgument("module", InputArgument::REQUIRED, "Module or library name"),
            new InputOption("file", "f", InputOption::VALUE_OPTIONAL, "File/path to test"),
            new InputOption("show-output", "o", InputOption::VALUE_NONE, "Show output of PHPUnit tests"),
            new InputOption("pu-parameters","p",InputOption::VALUE_OPTIONAL,"PHPUnit parameters"),

        ));

        $this->setHelp(<<<EOT
The <info>unit-test</info> command will run the ClinicLE module/library unit tests against the current system in using the specified phpunit.xml configuration.
Providing test/bootstrap.php has been setup as below it will load PHP dependencies from the current system rather than require the dependencies to be installed in the module/library directory.

Example:

bootstrap.php

<?php

date_default_timezone_set('Europe/London');

\$autoloadPath = getenv("CLE_AUTOLOAD_PATH");
include_once (\$autoloadPath ? : __DIR__."/../vendor")."/autoload.php";
?>

<info>clinicle unit-test test/module -o</info>

EOT
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
    	$modName = $input->getArgument("module");
        $file = $input->getOption("file") ? $input->getOption("file") : "";
		$showOutput = $input->getOption("show-output");
    	$phpunitParameters = $input->getOption("pu-parameters") ? $input->getOption("pu-parameters") : "";

        $modLibPath = $this->getModLibPath($modName);
        if(!$modLibPath) {
            throw new DirectoryNotFoundException("Module/library '".$modName."' was not found");
        }

        CLE::log()->info("Running module '$modName' unit tests");

        chdir($modLibPath);
        $this->preventTestRunIssues($modLibPath);
        $execute = new Execute(
            array(
                "export CLE_AUTOLOAD_PATH=".CLE::sys()->getPath()."/vendor &&",
                "phpunit",
                "-c".$modLibPath."/phpunit.xml",
                $phpunitParameters ? : "",
                $modLibPath."/test/$file"
            )
        );

        $execute->setExceptionOnFail(true);
        $execute->setExceptionMessage("Unit test failure".($file ? "for $file":"")."\n");
        $execute->setOutputLogged($showOutput);

        $execute->exec();
    }
    function getModLibPath($modLibName) {
        $modPath = CLE::sys()->getSourceModulePath($modLibName);
        if(!is_dir($modPath)) {
            $libPath = CLE::sys()->getPath()."/vendor/".$modLibName;
            if(is_dir($libPath)) {
                return $libPath;
            }
            return null;
        }
        return $modPath;
    }
    private function preventTestRunIssues($modLibPath) {
        if(!is_dir($modLibPath."/test")) {
            throw new FileNotFoundException("test directory is missing in ".$modLibPath);
        }
        if(!file_exists($modLibPath."/phpunit.xml")) {
            throw new FileNotFoundException("phpunit.xml is missing in ".$modLibPath);
        }
    }
}
