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
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Resource\GroupSetup;
use ClinicLE\Search\Resource\OverrideAccessSearchablePages;
use ClinicLE\Search\Util\ParamSelectStatement;
use CLE;
use ClinicLE\System\SystemCreator\ApplicationSystemCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildSearchCommand extends Command
{
    function configure()
    {
        $this->setName('build-search')
            ->setDescription('Build search database')
            ->setDefinition(array(
                new InputOption("ignore","i",InputOption::VALUE_NONE,"Ignore non-existing form errors"),
                new InputOption("hours-since","s",InputOption::VALUE_OPTIONAL,"Only rebuild if has not been built in the last 's' hours"),
                new InputOption("search-group","g",InputOption::VALUE_OPTIONAL,"Only rebuild specified search group. e.g. status")
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $creator = new ApplicationSystemCreator();
        $sys = $creator->create();
        $spages = new OverrideAccessSearchablePages(CLE::sys()->getModules());

        $groupSet = new GroupSet();
        GroupSetup::setup($groupSet,$spages,true);

        $log = CLE::log($this);

        $tableNames = array();
        $runner = new QueryRunner($groupSet, $sys->getEvents());

        if($input->getOption("search-group")) {
            $specifiedGroup = $groupSet->getGroup($input->getOption("search-group"));
            if(!$specifiedGroup) {
                $groupNames = array_map(function($x) { return $x->getName(); },$groupSet->getGroups());
                throw new \InvalidArgumentException("Unable to find group:".$input->getOption("search-group")." options include:".print_r($groupNames,1));
            }
            $categories = array($specifiedGroup->getCategoryName() => array($specifiedGroup->getName()));
        }
        else {
            $categories = $groupSet->getGroupCategories();
        }

        foreach($categories as $category => $groups) {
            $log->info("Building tables for search category '$category'");
            foreach($groups as $groupName) {
                $group = $groupSet->getGroup($groupName);
                if(is_a($group,"ClinicLE\Search\Group\EncounterPageGroup")) {
                    if(in_array($group->getEncounterType(), $tableNames)) {
                        continue;
                    }
                    $tableNames[]=$group->getEncounterType();
                }
                $group->setPrepareEnabled(true);
                if($input->getOption("hours-since")) {
                    $group->setHoursSince($input->getOption("hours-since"));
                }
                $groupQuery = new GroupQuery($group,array(),$runner);
                $select = new ParamSelectStatement();
                $log->info("Building search table for '".$groupName."'");

                try {
                    $group->prepare($select, $groupQuery);
                }
                catch(\Exception $ex) {
                    CLE::warn(get_class($ex)." error during build:".$ex->getMessage());
                }
            }
        }
    }

}
