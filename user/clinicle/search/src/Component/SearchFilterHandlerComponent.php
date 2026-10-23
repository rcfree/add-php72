<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:30
 */
namespace ClinicLE\Search\Component;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Search\SearchType\ListSearchType;
use ClinicLE\Search\Group\AbstractGroup;
use ClinicLE\Search\Resource\SearchServices;

class SearchFilterHandlerComponent extends \ADD_Component
{
    function getType() {
        return "SearchFilterHandler";
    }
    public function expectedPermissions(&$expPerms) {
        $expPerms[]="view";
        $expPerms[]="create";
    }

    function beforePopulate() {
        $add = CLE::sys();

        $db = CLE::db('dictionary');
        $edb = CLE::db('encounter');
        list($this->action, $this->pim) = $add -> assertParams(array('a', 'pim'));
        $this->action = $add -> assertParam('a');

        if($this->action=="edit") {
            $fieldPos = $add->assertParam("id");
            $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

            $groupId = $add -> assertParam('parentid');

            $searchData = $ssData->getSearchData();
            $filters = $ssData->getSearchGroupPart($groupId,"filters");
            $data = $filters[$fieldPos];

            $data['filter_field'] = $data['field'];
            $this->getPage()->setData($data);
        }
        if($this->action=="delete") {
            $fieldPos = $add->assertParam("id");

            $searchGroup = $add -> assertParam('pim');
            $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
            $groupFilters = $ssData->getSearchGroupPart($searchGroup,"filters");
            unset($groupFilters[$fieldPos]);
            $ssData->storeSearchGroupPart($searchGroup,"filters",$groupFilters);
        }
    }
    function menuPath(&$menuPath)
    {
        $menuPath="search/filter";
    }

    function afterGenerate() {
        $html = CLE::web()->getContent();
        $html->addElement('input',array('type'=>'hidden','value'=>CLE::sys()->getParam(\ADD_Component_Modal::ACTION_GET),'name'=>\ADD_Component_Modal::ACTION_POST));
        $html->addElement('input',array('type'=>'hidden','value'=>CLE::sys()->getParam("parentid"),'name'=>"__group"));
        $html->addElement('input',array('type'=>'hidden','value'=>CLE::sys()->getParam("id"),'name'=>"__id"));
    }

    function submit($values) {
        $form = $this->getPage()->getComponent('Form');
        $groupId = $form->getUncleanValue("__group");
        $action = $form->getUncleanValue("__action");

        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
        $group = $ssData->getGroupWithId($groupId);

        if ($action=="create") {
            $groupFilters = $ssData->getSearchGroupPart($groupId,"filters");
            $groupFilters[]=$this->extractFilter($values,$group);
            $ssData->storeSearchGroupPart($groupId,"filters",$groupFilters);
            return;
        }
        if($action=="edit") {
            $id = $form->getUncleanValue("__id");
            $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

            $groupFilters = $ssData->getSearchGroupPart($groupId,"filters");
            $groupFilters[$id]=$this->extractFilter($values,$group);

            $ssData->storeSearchGroupPart($groupId,"filters",$groupFilters);
        }
    }
    function extractFilter($values, AbstractGroup $group) {
        $SearchType = $group->getSearchType($values['filter_field']);
        $op = $values['operation'];
        $opTitle = $SearchType->getOperationTitle($op);
        $filterValues = $this->extractFilterValues($values);

        $filterValuesTitles = array();

        if(is_a($SearchType,ListSearchType::class)) {
            $SearchType->getField()->finaliseField($this->getPage());
            $options = $SearchType->getField()->getOptions();
            foreach($filterValues as $filterValue) {
                $filterValuesTitles[] = $options->findLabelWithValue($filterValue);
            }
        }
        else {
            $filterValuesTitles = $filterValues;
        }
        return array(
            "field"=>$values['filter_field'],
            "field_title"=>$SearchType->getFieldTitle(),
            "operation_title"=>$opTitle,
            "operation"=>$values['operation'],
            "values"=>$filterValues,
            "values_title"=>$filterValuesTitles,
            "param"=>$values['param']
        );
    }
    function extractFilterValues($values)
    {
        if (isset($values['value'])) {
            if (is_array($values['value'])) {
                return array_keys($values['value']);
            } else {
                return array($values['value']);
            }
        }
        if (isset($values['from_value']) || isset($values['to_value'])) {
            return array($values['from_value'], $values['to_value']);
        }
        return array();
    }
}