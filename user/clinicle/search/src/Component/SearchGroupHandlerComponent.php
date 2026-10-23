<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:30
 */
namespace ClinicLE\Search\Component;
use CLE;
use ClinicLE\Search\Resource\SearchServices;


class SearchGroupHandlerComponent extends \ADD_Component
{
    function getType() {
        return "SearchGroupHandler";
    }
    public function expectedPermissions(&$expPerms) {
        $expPerms[]="view";
        $expPerms[]="create";
        $expPerms[]="custom_search";
        $expPerms[]="audit_search";
    }
    public function menuPath(&$menuPath)
    {
        $menuPath = "search/group";
    }

    function beforePopulate() {
        $add = CLE::sys();

        $this->action = $add -> assertParam('a');


        if($this->action=="delete") {
            $groupId = $add -> assertParam('id');
            $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
            $ssData->removeSearchGroup($groupId);
        }

    }
    function afterGenerate() {
        $html = CLE::web()->getContent();
        $html->addElement('input',array('type'=>'hidden','value'=>CLE::sys()->getParam(\ADD_Component_Modal::ACTION_GET),'name'=>\ADD_Component_Modal::ACTION_POST));
    }

    function submit($values) {
        $form = $this->_page->getComponent('Form');
        $pim = $form->getUncleanValue("__pim");
        $this->pageInModule = CLE::sys()->getPageFromPim($pim);

        $action = $form->getUncleanValue("__action");

        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

        $groupName = $values['search_group'];

        if ($action=="create") {
            $groupSet = CLE::svc(\ClinicLE\Search\Resource\SearchServices::GROUP_SET);
            $ssData->initSearchGroup($groupName,$groupSet->getGroupDescription($groupName));
            return;
        }

    }
}