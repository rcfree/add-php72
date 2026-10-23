<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 20/04/17
 * Time: 09:13
 */

namespace ClinicLE\Search\Command;


use ClinicLE\Command\AbstractCommand;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Common\Util;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Output\EmailSearchExportOutput;
use ClinicLE\Search\Output\SearchExportOutput;
use ClinicLE\Search\Output\SerializedPagedOutput;
use ClinicLE\Search\Output\StandardPagedOutput;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Resource\GroupSetup;
use ClinicLE\Search\Resource\OverrideAccessSearchablePages;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\Util\JsonFileLoader;
use ClinicLE\Search\Util\ParamSelectStatement;
use CLE;
use ClinicLE\System\SystemCreator\ApplicationSystemCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Rhumsaa\Uuid\Uuid;
use ClinicLE\System\Service\CoreServices;
class RunSearchCommand extends Command
{
    protected function configure()
    {
        $this->setName('run-search')
            ->setDescription('Run data search and create output file')
            ->setDefinition(array(
                new InputOption("search-id","s",InputOption::VALUE_REQUIRED,"Search ID used to identify searchFile output path relative to system config Search -> definition_path"),
                new InputOption( "complete-file","c",InputOption::VALUE_NONE,"Create .complete file to indicate export is complete"),
                new InputOption ("use-definition","d",InputOption::VALUE_NONE,"Use definition file created in search path (<path_to_search_definitions>/<search_id>/definition)"),
                new InputOption ("page-no","p",InputOption::VALUE_OPTIONAL,"Page number for paged data"),
                new InputOption("format","f",InputOption::VALUE_OPTIONAL,"File format to output. Options included serialized (used internally by the web-services) or csv (the default)"),
                new InputOption("send-email","e",InputOption::VALUE_OPTIONAL,"Send notification email to specified email address"),
                new InputOption("output-filename","",InputOption::VALUE_REQUIRED,"Output file name")
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = CLE::log($this);
        $creator = new ApplicationSystemCreator();
        $creator->create();

        $spagesSvc = CLE::svc(SearchServices::SEARCHABLE_PAGES);
        $spages = $spagesSvc->getAllSearchablePages();

        $pdemogPage = $spagesSvc->getModules()->getPageInModule("patient","demographics");
        $pdemogPage->setPermissions(array("ident_search"=>true,"anon_search"=>true));

        foreach($spages as $spage) {
            $spage->setPermission("search",true);
        }
        $groupSet = new GroupSet();
        GroupSetup::setup($groupSet, $spagesSvc);

        if($input->getOption("use-definition")) {
            $definitionPath = CLE::sys()->getPath()."/".CLE::sys()->getConfig()->getSection("Search")->get("definition_path");

            $definitionFile = $definitionPath."/".$input->getOption("search-id")."/definition";
            $loader = new JsonFileLoader($definitionFile);
            $searchData = $loader->load();
        }
        else {
            $searchData = null;
        }

        $factory = new DataQueryRunnerFactory($groupSet);
        $runner = $factory->createRunner($searchData);

        $output = $this->createOutput($runner,$input);

        $output->outputResponse();
    }
    function createOutput(QueryRunner $runner, InputInterface $input) {
        $searchConfig = CLE::sys()->getConfig()->getSection("Search");
        $sendEmail = $input->getOption("send-email");

        if($input->getOption("format")=="serialized") {
            $output = new SerializedPagedOutput($runner);
            $output->setPageNo($input->getOption("page-no"));
            $output->setDefinitionPath($searchConfig->get("definition_path"));
            $output->setSearchId($input->getOption("search-id"));

            return $output;
        }

        $output = $input->getOption("send-email") ? new EmailSearchExportOutput($runner) : new SearchExportOutput($runner);
        if(!$input->getOption("output-filename")) {
            throw new \InvalidArgumentException("Required --output-filename option");
        }
        $output->setFileName($input->getOption("output-filename"));
        $output->setDefinitionPath($searchConfig->get("definition_path"));
        $output->setExportPath($searchConfig->get("export_path"));
        $output->setSearchId($input->getOption("search-id"));

        if($sendEmail) {
            $output->setSiteHost(CLE::sys()->config("site_host"));
            $output->setEmailAddress($input->getOption("send-email"));
            $output->setEmailer(CLE::svc(CoreServices::EMAILER));
        }
        return $output;
    }

}