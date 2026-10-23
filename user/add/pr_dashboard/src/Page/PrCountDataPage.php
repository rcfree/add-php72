<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 10/10/17
 * Time: 21:29
 */

namespace ADD\PR\Page;


use ADD\PR\SearchConfig;
use ADD\PR\SearchContent;
use ADD\PR\Util\FilterSearchContent;
use ClinicLE\Search\Output\StandardPagedOutput;
use ClinicLE\Search\Resource\SearchServices;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PrCountDataPage extends AbstractCountDataPage
{
    private $messages=array();
    function createMenuPath()
    {
        return "pr/count-data";
    }

    function processGetMethod(Request $request) {
        $data = array();

        $config = new SearchConfig($this->getFilePath());

        foreach($config->getSections() as $searchFile => $searchSection) {
            $content = new SearchContent(
                \CLE::svc(SearchServices::GROUP_SET),$this->getFilePath()."/searches/"
                .$searchFile
                .".json");

            $filterSearchContent = new FilterSearchContent($content);
            $runner = $filterSearchContent->createRunner($request->query);
            $this->appendUniqueMessages($filterSearchContent);

            $headings = $searchSection->getSetting("headings");
            $output = new StandardPagedOutput($runner);
            $output->setPageNo(1);

            $searchData = $output->outputResponse();
            if($headings) {
                $hcount=0;
                foreach($headings as $heading) {
                    $searchData['data'][0][$hcount]['label']=$heading;
                    $hcount++;
                }
            }
            $data[$searchFile]=$searchData;

        }

        $response = array("data"=>$data);

        if(count($this->messages)>0) {
            \CLE::web()->addMessage("Filtering treatment programmes on ".implode(" AND ", $this->messages));
        }
        else {
            \CLE::web()->addMessage("All treatment programmes included");
        }
        \CLE::info("messages",$this->messages);
        return $response;
    }
    function appendUniqueMessages(FilterSearchContent $content) {
        \CLE::info("append dbms:",$content->getFilterMessages());
        if(count($content->getFilterMessages())==0) {
            return;
        }
        $this->messages=array_unique(array_merge($content->getFilterMessages(),$this->messages));
    }
}