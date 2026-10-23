<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 13/10/17
 * Time: 21:31
 */

namespace ADD\PR\Page;


use ADD\PR\SearchContent;
use ADD\PR\Util\FilterSearchContent;
use ClinicLE\Data\Query\QueryRunner;
use ClinicLE\Search\Output\DefinitionOutput;
use ClinicLE\Search\Page\SearchRunPage;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Resource\SearchServices;
use Symfony\Component\HttpFoundation\ParameterBag;

class PrExportDataPage extends SearchRunPage
{
    function retrieveSearchData(ParameterBag $params)
    {
        $content = new SearchContent(\CLE::svc(SearchServices::GROUP_SET), $this->getFilePath() . "/searches/export.json");
        $filterSearchContent = new FilterSearchContent($content);
        $runner = $filterSearchContent->createRunner($params);
        $exportOptions = $params->get("export_options");
        $groupsToInclude = array_keys(json_decode($exportOptions,true));
        $ddb = \CLE::db('dictionary');
        foreach($groupsToInclude as $groupToInclude) {
            $matches = array();
            preg_match("/^(.+)?_([s|f|x])$/",$groupToInclude, $matches);
            array_shift($matches);
            list($groupName, $exportType) = $matches;
            $groupQuery = $runner->getGroupQueryByName($groupName);
            if ($exportType=="f") {
                $groupQuery->addAllAttributes();
            }
            elseif ($exportType=="s") {
                $ditems = $ddb->getDictionaryItemsInGroup("pr_export_fields",$groupName);
                usort($ditems, function ($item1, $item2) {
                    if ($item1['weight'] == $item2['weight']) return 0;
                    return $item1['weight'] < $item2['weight'] ? -1 : 1;
                });

                $attrs = array_map(function($x) { return $x['title']; },$ditems);
                $groupQuery->addAttributes($attrs);
            }
		
        }

        $output = new DefinitionOutput($runner);

        return $output->outputResponse();
    }

    function createMenuPath()
    {
        return "pr/export-data";
    }
}
