<?php

namespace ClinicLE\Options\Resource;
use CLE;
use ClinicLE\System\System;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/09/16
 * Time: 11:15
 */
class HomeToolManager {
    private $hometools = array();

    /**
     * @return array
     */
    public function getHometools()
    {
        return $this->hometools;
    }

    function populateHometools(System $system) {
        $hometoolTypes = array();
        foreach($system->getModules()->asArray() as $module) {
            $hometoolTypes = array_merge($hometoolTypes,$module->getTemplate()->getSettingList("hometools", " "));
        }

        foreach($hometoolTypes as $hometoolType) {
            $this->createHomeTool($hometoolType,$system);
        }
    }
    function createHomeTool($hometoolType, $system) {
        $hometool = $system->newFactoryInstance(OptionsFactories::HOMETOOL,$hometoolType);
        $this->addHometool($hometool);
        return $hometool;
    }
    private function sortByToolWeight($a,$b) {
        $aweight = $a->toolWeight();
        $bweight = $b->toolWeight();
        if($aweight==$bweight) {
            return 0;
        }
        return ($aweight<$bweight) ? -1 : 1;
    }
    function renderHometools($html) {
        $tools = array_values($this->getHometools());
        uasort($tools,array($this,"sortByToolWeight"));

        $html->startElement("div",array("id"=>"home-tool"));

        $html->startElement("div");

        $toolAccessCount = 0;
        foreach($tools as $tool) {
            if(!$tool->isAvailable()) {
                continue;
            }

            $toolHtml = $tool->createTool();

            if (!$toolHtml) {
                continue;
            }
            $toolAccessCount++;

            $toolClass = "tool-block";
            if(method_exists($tool,"toolClass")) {
                $toolClass.=" ".$tool->toolClass();
            }

            $html->startElement("div",array("class"=>$toolClass));
            $html->addTextElement("div",$tool->toolTitle(),array("class"=>"tool-block-title"));
            $html->startElement("div",array("class"=>"tool-block-content"));
            $html->addHtml($toolHtml);
            $html->endElement("div");
            $html->endElement("div");

        }

        $html->endElement("div");
        $html->endElement("div");
    }
    function addHomeTool($hometool) {
        $this->hometools[]=$hometool;
    }
    function getHometool($hometoolName) {
        return $this->hometools[$hometoolName];
    }
}