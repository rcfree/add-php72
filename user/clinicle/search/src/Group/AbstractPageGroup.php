<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 15:52
 */

namespace ClinicLE\Search\Group;
use ClinicLE\Search\SearchType\SearchTypeFactory;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\Search\Query\GroupQuery;
abstract class AbstractPageGroup extends AbstractGroup
{
    private $searchTypesInit = false;
    private $form;
    private $parentPage;
    private $page;

    /**
     * @return mixed
     */
    public function getParentPage()
    {
        return $this->parentPage;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return boolean
     */
    public function isSearchTypesInit()
    {
        return $this->searchTypesInit;
    }

    /**
     * @param boolean $searchTypesInit
     */
    public function setSearchTypesInit($searchTypesInit)
    {
        $this->searchTypesInit = $searchTypesInit;
    }
    function __construct($page) {
        $moduleAlias = $page->getModule()->getAlias();
        $this->setCategoryName($moduleAlias);
        $sectionPage = $page->getParentPage();
        $sectionTitle = $sectionPage ? $sectionPage->getSetting("title",$sectionPage->getName()) : null;
        $pageTitle = $page->getSetting("title",$page->getName());
        $this->setTitle(($sectionTitle ? $sectionTitle.": " : "").$pageTitle);
        $form = $page->getComponent("Form");

        $this->page = $page;
        if($sectionPage) {
            $this->parentPage = $sectionPage;
            $this->setName($moduleAlias.".".$sectionPage->getName().".".$page->getName());
        }
        else {
            $this->setName($moduleAlias.".".$page->getName());
        }
        $this->form = $form;
    }

    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $this->initSearchTypes();
        parent::prepare($select, $groupQuery);
    }
    function getSearchType($type) {
        $this->initSearchTypes();
        return parent::getSearchType($type);
    }
    function getSearchTypes() {
        $this->initSearchTypes();
        return parent::getSearchTypes();
    }
    function initSearchTypes() {
        if($this->searchTypesInit) {
            return;
        }
        if(!$this->form->isSetup()) {
            $this->form->setup();
        }

        foreach($this->getFieldNames() as $fieldName) {
            $field = $this->form->getField($fieldName);
            if(!$field->isStored()) {
                continue;
            }
            $SearchType = SearchTypeFactory::createFromField($field);
            $this->addSearchType($SearchType);
        }
        $this->extendSearchTypes();
        $this->searchTypesInit = true;
    }
    function getFieldNames() {
        return array_keys($this->form->getFieldsByName());
    }
    function extendSearchTypes() {

    }
}