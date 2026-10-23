<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 02/02/17
 * Time: 11:15
 */

namespace ClinicLE\Search\Resource;


use ClinicLE\Module\ModuleCollection;

class SearchablePages
{
    private $searchable;
    protected $ignoreModuleAliases = array("search","patient");
    function __construct(ModuleCollection $modules)
    {
        $this->modules = $modules;
    }

    function getAllSearchablePages()
    {
        if($this->searchable) {
            return $this->searchable;
        }
        $spages = array();

        foreach ($this->modules as $module) {
            foreach ($module->getPages() as $page) {
                if ($this->isSearchablePage($page)===false) {
                    continue;
                }

                $spages[]=$page;
            }
        }

        $this->searchable = $spages;
        return $spages;
    }
    function getAvailableModules() {
        $spages = $this->getAllSearchablePages();
        $smodules = array();
        foreach($spages as $spage) {
            if($this->hasSearchAccess($spage) === false) {
                continue;
            }
            if(isset($smodules[$spage->getModule()->getName()])) {
                continue;
            }
            $smodules[$spage->getModule()->getName()]=$spage->getModule();
        }
        return array_values($smodules);
    }

    function getAvailablePages() {
        $spages = $this->getAllSearchablePages();

        $availSpages = array();
        foreach($spages as $spage) {
            if($this->hasSearchAccess($spage) === false) {
                continue;
            }
            $availSpages[]=$spage;
        }
        return $availSpages;
    }
    function isSearchablePage($page) {
        if (!method_exists($page,"hasComponent")) {
            return false;
        }

        if(!$page->hasComponent("Form") || !is_a($page->getComponent("Form"),
                "ADD_Component_Form_DB")) {
            return false;
        }
        if(!$page->hasComponent("Encounter")) {
            return false;
        }
        $encComponent = $page->getComponent("Encounter");

        if(is_a($encComponent,"ADD_Component_Encounter_Domain")) {
            return false;
        }
        if(is_a($encComponent,"ADD_Component_Encounter_Section")) {
            return false;
        }
        if(!$page->getSetting("encounter")) {
            return false;
        }
        if($page->getSetting("exclude_from_search")) {
            return false;
        }
        return true;
    }

    function getModules() {
        return $this->modules;
    }
    function hasSearchAccess($page) {
        return $page->getPermission("search");
    }
}
