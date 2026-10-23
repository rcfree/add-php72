<?php
namespace ADD\PR\Field;

use ADD\PR\LoaderOnlyLegend;
use ADD\PR\MultiGridHeader;
use ADD\PR\MultiGridLegend;
use ADD\PR\MultiGridTable;
use ADD\PR\SearchConfig;
use ClinicLE\Client\Container\Container;


/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:12
 */
class SearchGridContainerField extends \ADD_Field_Container
{
    private $searchConfig;
    function getSearchConfig() {
        return $this->searchConfig;
    }
    function startFieldHtml($page)
    {
        $this->container = new Container($this->getName(), "EncounterGridController");

        $this->searchConfig = new SearchConfig($page->getFilePath());

        #required Table parameter not used
        $settings = $this->getSettings();

        $this->container->appendProperties($settings);
        $this->container->startHtml($page->html());
        $legend = new LoaderOnlyLegend("control.data.grid");

        $page->html()->addHtml($legend->createHtml());

    }

    function endFieldHtml($page)
    {
        $this->container->endHtml($page->html());
    }
}